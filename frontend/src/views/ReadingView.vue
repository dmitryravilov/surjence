<template>
  <div class="min-h-screen py-16 sm:py-20 px-4 sm:px-6 lg:px-8 max-w-5xl mx-auto">
    <!-- Header -->
    <header class="mb-16 sm:mb-20 text-center">
      <div class="flex items-center justify-center gap-3 mb-3">
        <!-- Logo Icon -->
        <img
            src="/surjence.svg"
            alt="Surjence logo"
            class="w-10 h-10 sm:w-12 sm:h-12"
        />
        <!-- Title -->
        <h1 class="text-display sm:text-6xl font-light text-coffee-900 tracking-tight">
          Surjence
        </h1>
      </div>
      <p class="text-title sm:text-2xl text-coffee-600 font-light">
        Mindful News, Brewed Slowly
      </p>
    </header>

    <!-- Loading State -->
    <div v-if="loading" class="text-center py-26">
      <div class="inline-block animate-pulse text-coffee-500 text-body-large font-light">
        Preparing your reading...
      </div>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="text-center py-26">
      <p class="text-coffee-700 mb-6 text-body-large">{{ error }}</p>
      <button
        @click="fetchHeadlines"
        class="px-8 py-4 bg-coffee-200 text-coffee-800 rounded-xl hover:bg-coffee-300 transition-all duration-calm text-lg font-medium"
      >
        Try Again
      </button>
    </div>

    <!-- Headlines -->
    <div v-else-if="headlines.length > 0 && !showEndState" class="space-y-12 sm:space-y-16">
      <div
        v-for="(headline, index) in headlines"
        :key="headline.id"
        class="headline-card"
        :style="{ animationDelay: `${index * 120}ms` }"
      >
        <!-- Theme Badge -->
        <div v-if="headline.theme" class="mb-6">
          <span
            class="theme-badge"
            :style="{ 
              backgroundColor: headline.theme.color + '15', 
              color: headline.theme.color,
              borderColor: headline.theme.color + '30',
              borderWidth: '1px'
            }"
          >
            {{ headline.theme.name }}
          </span>
        </div>

        <!-- Title -->
        <h2 class="text-headline sm:text-4xl font-normal text-coffee-900 mb-6 leading-tight tracking-tight">
          {{ headline.title }}
        </h2>

        <!-- Source -->
        <p class="text-sm text-coffee-500 mb-6 font-light">
          {{ headline.source }}
        </p>

        <!-- Description -->
        <p v-if="headline.description" class="text-body-large text-coffee-700 mb-8 leading-relaxed font-light">
          {{ headline.description }}
        </p>

        <!-- Reflection -->
        <div class="mt-8 pt-8 border-t border-coffee-200/60">
          <p class="reflection-text">
            {{ headline.reflection }}
          </p>
        </div>

        <!-- Link -->
        <div class="mt-8">
          <a
            :href="headline.url"
            target="_blank"
            rel="noopener noreferrer"
            class="text-coffee-600 hover:text-coffee-800 underline text-base transition-colors duration-calm font-light"
          >
            Read full article →
          </a>
        </div>
      </div>

      <!-- End Reading Button -->
      <div class="text-center pt-12">
        <button
          @click="showEndState = true"
          class="px-10 py-5 bg-coffee-800 text-white rounded-xl hover:bg-coffee-900 transition-all duration-calm text-lg font-medium shadow-sm hover:shadow-md"
        >
          I'm Done Reading
        </button>
      </div>
    </div>

    <!-- End State -->
    <div v-else-if="showEndState" class="end-state">
      <div class="max-w-lg mx-auto">
        <div class="text-7xl sm:text-8xl mb-8">☕</div>
        <h2 class="text-headline sm:text-4xl font-light text-coffee-900 mb-6 tracking-tight">
          That's enough for today.
        </h2>
        <p class="text-body-large text-coffee-700 mb-10 leading-relaxed font-light">
          You've taken in what you needed. Close this app and return to your day with presence.
        </p>
        <button
          @click="resetReading"
          class="px-8 py-4 bg-coffee-200 text-coffee-800 rounded-xl hover:bg-coffee-300 transition-all duration-calm text-lg font-medium"
        >
          Read Again
        </button>
      </div>
    </div>

    <!-- Empty State -->
    <div v-else class="text-center py-26">
      <p class="text-coffee-600 text-body-large font-light">No content available at this time.</p>
    </div>
  </div>
</template>

<script>
import axios from 'axios'

export default {
  name: 'ReadingView',
  data() {
    return {
      headlines: [],
      loading: true,
      error: null,
      showEndState: false,
      apiUrl: import.meta.env.VITE_API_URL || 'http://localhost:8000'
    }
  },
  mounted() {
    this.fetchHeadlines()
  },
  methods: {
    async fetchHeadlines() {
      this.loading = true
      this.error = null
      this.showEndState = false

      try {
        const response = await axios.get(`${this.apiUrl}/api/v1/headlines`)
        this.headlines = response.data.data || []
      } catch (err) {
        this.error = 'Unable to fetch headlines. Please try again later.'
        console.error('Error fetching headlines:', err)
      } finally {
        this.loading = false
      }
    },
    resetReading() {
      this.showEndState = false
      this.fetchHeadlines()
    }
  }
}
</script>

<style scoped>
.headline-card {
  animation: fadeInUp 0.8s cubic-bezier(0.4, 0, 0.2, 1) both;
}

@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(30px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* Smooth scrolling */
html {
  scroll-behavior: smooth;
}
</style>
