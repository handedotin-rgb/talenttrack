FROM php:8.2-cli-alpine

# Install SQLite dependencies & PDO SQLite extension
RUN apk add --no-cache sqlite-dev \
    && docker-php-ext-install pdo_sqlite

WORKDIR /var/www/html

# Copy full application code (all folders are in git)
COPY . .

# Ensure storage directories exist and have write permissions for SQLite and uploads
RUN mkdir -p database assets/uploads/resumes assets/uploads/avatars logs \
    && chmod -R 777 database assets/uploads logs

ENV PORT=10000
EXPOSE 10000

CMD ["sh", "-c", "php -S 0.0.0.0:${PORT:-10000} index.php"]
