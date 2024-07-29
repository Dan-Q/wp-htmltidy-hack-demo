FROM wordpress:php8.3

# You know how if you don't install the 'zip' extension, PHP can still make ZIP files... but they're uncompressed?
# Turns out a similar thing happens with the 'tidy' extension. PHP still indents the code nicely... but doesn't do anything else.
# So make sure the 'tidy' extension is installed! (you can test using `php -i | grep tidy`)
RUN apt update && apt install -y libtidy-dev
RUN docker-php-ext-install tidy
