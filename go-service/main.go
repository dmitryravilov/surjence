package main

import (
	"context"
	"crypto/sha256"
	"encoding/hex"
	"encoding/json"
	"fmt"
	"io"
	"log/slog"
	"net/http"
	"net/url"
	"os"
	"sort"
	"strings"
	"time"

	"github.com/gin-gonic/gin"
	"github.com/joho/godotenv"
)

type NewsDataResponse struct {
	Status       string    `json:"status"`
	TotalResults int       `json:"totalResults"`
	Results      []Article `json:"results"`
	NextPage     string    `json:"nextPage,omitempty"`
}

type Article struct {
	ArticleID   string   `json:"article_id"`
	Title       string   `json:"title"`
	Link        string   `json:"link"`
	Description string   `json:"description"`
	Content     string   `json:"content"`
	PubDate     string   `json:"pubDate"`
	ImageURL    string   `json:"image_url,omitempty"`
	VideoURL    string   `json:"video_url,omitempty"`
	Creator     []string `json:"creator,omitempty"`
	Keywords    []string `json:"keywords,omitempty"`
	SourceID    string   `json:"source_id,omitempty"`
	SourceName  string   `json:"source_name,omitempty"`
	SourceURL   string   `json:"source_url,omitempty"`
	SourceIcon  string   `json:"source_icon,omitempty"`
}

type ProcessedHeadline struct {
	Hash           string    `json:"hash"`
	Title          string    `json:"title"`
	Source         string    `json:"source"`
	URL            string    `json:"url"`
	Description    string    `json:"description"`
	PublishedAt    string    `json:"publishedAt"`
	Sentiment      string    `json:"sentiment"`
	SentimentScore float64   `json:"sentimentScore"`
	Keywords       []string  `json:"keywords"`
	ProcessedAt    time.Time `json:"processedAt"`
}

type HeadlinesResponse struct {
	Headlines []ProcessedHeadline `json:"headlines"`
	Count     int                 `json:"count"`
	FetchedAt time.Time           `json:"fetchedAt"`
}

var (
	newsDataAPIKey string
	newsDataBase   = "https://newsdata.io/api/1"
	logger         *slog.Logger
)

func init() {
	_ = godotenv.Load()
	newsDataAPIKey = os.Getenv("NEWSDATA_API_KEY")
	if newsDataAPIKey == "" {
		slog.Error("NEWSDATA_API_KEY environment variable is required")
		os.Exit(1)
	}

	logger = slog.New(slog.NewJSONHandler(os.Stdout, &slog.HandlerOptions{
		Level: slog.LevelInfo,
	}))
	slog.SetDefault(logger)
}

func main() {
	gin.SetMode(gin.ReleaseMode)
	r := gin.Default()

	r.HEAD("/health", func(c *gin.Context) {
		c.Status(http.StatusOK)
	})

	r.GET("/api/v1/headlines/raw", getHeadlines)

	port := os.Getenv("PORT")
	if port == "" {
		port = "8080"
	}

	slog.Info("Go service starting", "port", port)
	if err := r.Run(":" + port); err != nil {
		slog.Error("Failed to start server", "error", err)
		os.Exit(1)
	}
}

func getHeadlines(c *gin.Context) {
	ctx, cancel := context.WithTimeout(c.Request.Context(), 30*time.Second)
	defer cancel()

	headlines, err := fetchAndProcessHeadlines(ctx)
	if err != nil {
		slog.ErrorContext(ctx, "Failed to fetch headlines", "error", err)
		c.JSON(http.StatusInternalServerError, gin.H{
			"error": err.Error(),
		})
		return
	}

	response := HeadlinesResponse{
		Headlines: headlines,
		Count:     len(headlines),
		FetchedAt: time.Now(),
	}

	c.JSON(http.StatusOK, response)
}

func fetchAndProcessHeadlines(ctx context.Context) ([]ProcessedHeadline, error) {
	articles, err := fetchFromNewsData(ctx)
	if err != nil {
		return nil, err
	}

	processed := make([]ProcessedHeadline, 0, len(articles))
	for _, article := range articles {
		select {
		case <-ctx.Done():
			return nil, ctx.Err()
		default:
		}

		hash := generateHash(article.Title, article.Link)

		sentiment, score := analyzeSentiment(article.Title, article.Description)
		keywords := extractKeywords(article.Title, article.Description)

		// Use source_name if available, otherwise use source_id
		sourceName := article.SourceName
		if sourceName == "" {
			sourceName = article.SourceID
		}
		if sourceName == "" {
			sourceName = "Unknown Source"
		}

		processed = append(processed, ProcessedHeadline{
			Hash:           hash,
			Title:          article.Title,
			Source:         sourceName,
			URL:            article.Link,
			Description:    article.Description,
			PublishedAt:    article.PubDate,
			Sentiment:      sentiment,
			SentimentScore: score,
			Keywords:       keywords,
			ProcessedAt:    time.Now(),
		})
	}

	return processed, nil
}

func fetchFromNewsData(ctx context.Context) ([]Article, error) {
	// Require mindfulness-related keywords to appear in the title
	query := "(mindfulness OR meditation OR wellness OR \"inner peace\" OR \"self-confidence\")"

	baseURL := fmt.Sprintf("%s/latest", newsDataBase)
	reqURL, err := url.Parse(baseURL)
	if err != nil {
		return nil, fmt.Errorf("failed to parse base URL: %w", err)
	}

	params := url.Values{}
	params.Add("apikey", newsDataAPIKey)
	params.Add("qInTitle", query)
	params.Add("category", "health")
	params.Add("language", "en")

	reqURL.RawQuery = params.Encode()

	req, err := http.NewRequestWithContext(ctx, http.MethodGet, reqURL.String(), nil)
	if err != nil {
		return nil, fmt.Errorf("failed to create request: %w", err)
	}

	client := &http.Client{Timeout: 30 * time.Second}
	resp, err := client.Do(req)
	if err != nil {
		return nil, fmt.Errorf("failed to fetch from NewsData.io: %w", err)
	}
	defer resp.Body.Close()

	if resp.StatusCode != http.StatusOK {
		body, _ := io.ReadAll(resp.Body)
		return nil, fmt.Errorf("NewsData.io returned status %d: %s", resp.StatusCode, string(body))
	}

	var apiResponse NewsDataResponse
	if err := json.NewDecoder(resp.Body).Decode(&apiResponse); err != nil {
		return nil, fmt.Errorf("failed to decode response: %w", err)
	}

	if apiResponse.Status != "success" {
		return nil, fmt.Errorf("NewsData.io returned status: %s", apiResponse.Status)
	}

	if len(apiResponse.Results) == 0 {
		slog.Info("No articles found matching mindfulness keywords")
		return []Article{}, nil
	}

	return apiResponse.Results, nil
}

func generateHash(title, url string) string {
	data := fmt.Sprintf("%s|%s", title, url)
	hash := sha256.Sum256([]byte(data))
	return hex.EncodeToString(hash[:])
}

func analyzeSentiment(title, description string) (string, float64) {
	var b strings.Builder
	b.Grow(len(title) + len(description) + 1)
	b.WriteString(strings.ToLower(title))
	b.WriteByte(' ')
	b.WriteString(strings.ToLower(description))
	text := b.String()

	positiveWords := map[string]bool{
		"good": true, "great": true, "excellent": true, "amazing": true,
		"wonderful": true, "positive": true, "success": true, "win": true,
		"achievement": true, "progress": true, "improve": true, "better": true,
		"hope": true, "joy": true, "happy": true, "celebrate": true,
		"breakthrough": true, "victory": true,
	}

	negativeWords := map[string]bool{
		"bad": true, "terrible": true, "awful": true, "horrible": true,
		"negative": true, "crisis": true, "fail": true, "loss": true,
		"decline": true, "worse": true, "problem": true, "issue": true,
		"concern": true, "fear": true, "worry": true, "attack": true,
		"violence": true, "death": true, "tragedy": true, "disaster": true,
	}

	positiveCount := 0
	negativeCount := 0
	words := strings.Fields(text)

	for _, word := range words {
		word = strings.Trim(word, ".,!?;:\"'()[]{}")
		if positiveWords[word] {
			positiveCount++
		}
		if negativeWords[word] {
			negativeCount++
		}
	}

	total := positiveCount + negativeCount
	if total == 0 {
		return "neutral", 0.0
	}

	score := float64(positiveCount-negativeCount) / float64(total)

	if score > 0.1 {
		return "positive", score
	} else if score < -0.1 {
		return "negative", score
	}
	return "neutral", score
}

func extractKeywords(title, description string) []string {
	var b strings.Builder
	b.Grow(len(title) + len(description) + 1)
	b.WriteString(strings.ToLower(title))
	b.WriteByte(' ')
	b.WriteString(strings.ToLower(description))
	text := b.String()

	stopWords := map[string]bool{
		"the": true, "a": true, "an": true, "and": true, "or": true,
		"but": true, "in": true, "on": true, "at": true, "to": true,
		"for": true, "of": true, "with": true, "by": true, "from": true,
		"is": true, "are": true, "was": true, "were": true, "be": true,
		"been": true, "have": true, "has": true, "had": true, "do": true,
		"does": true, "did": true, "will": true, "would": true, "could": true,
		"should": true, "may": true, "might": true, "must": true, "can": true,
		"this": true, "that": true, "these": true, "those": true, "it": true,
		"its": true, "they": true, "them": true, "their": true, "there": true,
		"what": true, "which": true, "who": true, "whom": true, "whose": true,
		"where": true, "when": true, "why": true, "how": true,
	}

	wordFreq := make(map[string]int)
	words := strings.Fields(text)

	for _, word := range words {
		word = strings.Trim(word, ".,!?;:\"'()[]{}")
		if len(word) > 3 && !stopWords[word] {
			wordFreq[word]++
		}
	}

	type wordCount struct {
		word  string
		count int
	}

	sorted := make([]wordCount, 0, len(wordFreq))
	for word, count := range wordFreq {
		sorted = append(sorted, wordCount{word, count})
	}

	sort.Slice(sorted, func(i, j int) bool {
		return sorted[i].count > sorted[j].count
	})

	keywords := make([]string, 0, 5)
	for i := 0; i < len(sorted) && i < 5; i++ {
		keywords = append(keywords, sorted[i].word)
	}

	return keywords
}
