# Chat with Starter Story - RAG Implementation

A basic **Retrieval-Augmented Generation (RAG)** implementation for testing purposes, built to enable conversational interactions with [Starter Story YouTube video](https://www.youtube.com/@starterstory).

## 🚀 Technology Stack

- **Backend**: Symfony 7.3 (PHP 8.4)
- **Database**: PostgreSQL 17
- **Search Engine**: Elasticsearch 8.13.2
- **AI/ML**: OpenAI API with [LLPhant library](https://github.com/LLPhant/LLPhant)
- **Frontend**: Tailwind (4.1) & DaisyUI
- **Web Server**: Caddy

## 📋 Prerequisites

- Docker and Docker Compose
- [OpenAI](https://platform.openai.com/) API key
- [Supadata](https://supadata.ai/) API access (for YouTube data fetching)
- [Youtube](https://console.cloud.google.com/) API key

## 🖥️ Demo

You can try a demo [here](https://chat.eddaoust.com/)

## 🛠️ Installation Setup

### 1. Clone the Repository
```bash
git clone <repository-url>
cd ChatWithStarterStory
```

### 2. Environment Configuration
Copy the environment files and configure them:
```bash
cp .env .env.local
```

Add your API keys to `.env.local`:
```env
OPENAI_API_KEY=your_openai_api_key_here
SUPADATA_API_KEY=your_supadata_api_key_here
YOUTUBE_API_KEY=your_youtube_api_key_here
```

### 3. Start the Application
```bash
# Build and start all services
docker compose --env-file .env.docker up -d --build

# Access the PHP container
docker exec -ti php /bin/bash
```

### 4. Install Dependencies & Setup Database
Inside the PHP container:
```bash
# Install Composer dependencies
composer install

# Create database and run migrations
bin/console doctrine:database:create
bin/console doctrine:migrations:migrate

# Build Tailwind CSS (in a separate terminal)
bin/console tailwind:build --watch
```

### 5. Access the Application
- **Web Interface**: http://localhost:8080 (Caddy will proxy to the Symfony app)
- **Elasticsearch**: http://localhost:9200
- **Database**: PostgreSQL on default port with credentials from `.env.docker`

## 📊 Data Generation for Embeddings

The RAG system requires a three-step data preparation process:

### Step 1: Import YouTube Videos
```bash
bin/console app:import-youtube-videos
```
This command:
- Fetches videos from the Starter Story YouTube channel
- Retrieves video metadata (title, description, thumbnail, etc.)
- Stores video information in the PostgreSQL database
- Processes up to 100 videos in batches

### Step 2: Create Transcription Chunks
```bash
bin/console app:create-transcription-chunks
```
This command:
- Fetches transcriptions for each imported video using Supadata API
- Breaks transcriptions into manageable chunks with timestamps
- Creates `TranscriptionChunk` entities with content, offset, and duration
- Respects API rate limits with built-in delays

### Step 3: Generate Embeddings
```bash
bin/console app:generate-embeddings
```
This command:
- Processes transcription chunks that don't have embeddings
- Generates vector embeddings using OpenAI's embedding model
- Stores embeddings for semantic search capabilities
- Processes chunks in batches of 25 for optimal performance

## 🧠 How It Works

### RAG Architecture Overview

1. **Data Ingestion**: YouTube videos are imported and transcribed into searchable chunks
2. **Vector Storage**: Text chunks are converted to embeddings and stored in Elasticsearch
3. **Query Processing**: User questions are converted to embeddings for similarity search
4. **Context Retrieval**: Most relevant video chunks are retrieved based on semantic similarity
5. **Response Generation**: OpenAI LLM generates answers using retrieved context
6. **Result Presentation**: Responses include relevant video links with timestamps

### Data Flow
```
User Question → Embedding → Vector Search → Context Building →docker exec mariadb mysqldump -u root -p database > database_dump.sql LLM Query → Response + Video Links
```

## 🔧 Development Commands

### Docker Management
```bash
# Stop all services
docker compose down --remove-orphans

# View logs
docker compose logs -f

# Rebuild specific service
docker compose up -d --build php
```

### Asset Management
```bash
# Build Tailwind CSS
bin/console tailwind:build

# Watch for changes
bin/console tailwind:build --watch
```

## 📄 License

This project is for testing and educational purposes. Please ensure compliance with YouTube's Terms of Service and OpenAI's usage policies when using this application.
