FROM golang:1.22-alpine AS builder

WORKDIR /app

COPY go.mod go.sum ./
RUN go mod download

COPY . .

# Install curl and golangci-lint
RUN apk add --no-cache curl git bash \
    && curl -sSfL https://raw.githubusercontent.com/golangci/golangci-lint/master/install.sh \
       | sh -s -- -b /usr/local/bin

# Build your Go binary
RUN CGO_ENABLED=0 GOOS=linux go build -o main .

# Final stage
FROM alpine:3.20

RUN apk --no-cache add ca-certificates wget

WORKDIR /root/
COPY --from=builder /app/main .

EXPOSE 8080
HEALTHCHECK --interval=30s --timeout=10s --start-period=5s --retries=3 \
  CMD wget --no-verbose --tries=1 --spider http://localhost:8080/health || exit 1

CMD ["./main"]
