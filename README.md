## FlashCard

#### FlashCard: A Platform for Sharing Engineering Tips & Tricks

FlashCard is a collaborative platform for engineers to share and discover simple tips and tricks that enhance
productivity and innovation. Users can post tips, explore contributions, and engage in discussions to refine their
knowledge. Whether it's coding shortcuts, troubleshooting advice, or best practices, FlashCard provides a supportive
community for practical insights.

## Installation & Running FlashCard

### Step 1: Install Dependencies

#### Using Docker

To install the necessary dependencies using Docker, run the following command:

```bash
docker run --rm \
    -u "$(id -u):$(id -g)" \
    -v "$(pwd):/var/www/html" \
    -w /var/www/html \
    laravelsail/php83-composer:latest \
    composer install --ignore-platform-reqs
```

#### ON Linux

If you are using a Linux-based OS, you might need to prepend sudo to the command:

```bash
sudo docker run --rm \
    -u "$(id -u):$(id -g)" \
    -v "$(pwd):/var/www/html" \
    -w /var/www/html \
    laravelsail/php83-composer:latest \
    composer install --ignore-platform-reqs
```

### Step 2: Configure Environment Variables

Copy the example environment file and generate the application key:

```bash
cp .env.example .env
```

### Step 3: Start the Development Server

To start the development server using Laravel Sail, run:

```bash
./vendor/bin/sail up
```

### Step 4: Generate application key

Generate the application key:

```bash
./vendor/bin/sail artisan key:generate
```

### Step 5: Run Database Migrations and Seeders

Run the database migrations and seed the database:

```bash
./vendor/bin/sail artisan migrate --seed
```

Your FlashCard platform should now be up and running! Enjoy sharing and discovering tips and tricks with the engineering
community.

### Flashcard postman schema link:

https://documenter.getpostman.com/view/23973970/2sAXjSzUMd#977788a8-94aa-465b-944a-bdcb86b7272f