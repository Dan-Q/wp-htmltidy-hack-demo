## TidyPress

A hacky demonstration of how you can, with just a few lines in the `header.php` of your (non-FSE) WordPress theme,
configure [HTMLTidy](https://www.html-tidy.org/) to run across all of your output files, resulting in:

- Cleaner, tidier, more human-readable HTML code
- Code repaired and formatted to the standard you prefer (e.g. HTML5 `<tags>` without XHTML-styles self-closing `<tags/>` where they're not required)
- Updating logical emphasis tags e.g. `<b>` -> `<strong>`, `<i>` -> `<em>`
- Hoisting any inline `<style>` blocks to the `<head>`, and re-writing any repeated inline styles as classes
- Suppressing comments
- Repairing invalid HTML to save the browser from having to do it every time

Please don't just use this as-is without understanding the implications. Read this entire README and make sure you grok it first. [I'm happy to take questions](https://danq.me/contact/).

### Demonstration

To see this working:

1. Check out this codebase
2. Run `docker compose up` to create a new development WordPress site at https://localhost:8890/
3. Visit https://localhost:8890/ and go through the install (pick a site name, username, password, etc.)
4. Use your new credentials to install and activate the **TwentyTwentyOne** theme (http://localhost:8890/wp-admin/theme-install.php?search=twentytwentyone)
5. Visit http://localhost:8890/ and View Source; see that the code looks like regular old WordPress
6. Activate the **HTMLTidy21** theme (http://localhost:8890/wp-admin/themes.php), which is a child theme of **TwentyTwentyOne**.
7. Refresh http://localhost:8890/ and View Source again: note that the code has been tidied for consistency, readability, and standards! 🎉

#### Before

![HTML source, untidy](https://github.com/user-attachments/assets/fcb66529-7bca-4ddc-b34f-0564068175ea)

#### After

![HTML source, tidied](https://github.com/user-attachments/assets/dd34d8ba-655b-42bf-b470-62117c12b7e8)

#### How does this work?

**HTMLTidy21** is a child theme of **TwentyTwentyOne**. `src/header.php`, in this repo, overrides TwentyTwentyOne's `header.php` only.

It adds a new output buffer with a callback function: `ob_start( 'tidy_entire_page' );`. Because it's never explicitly finished, that output buffer is automatically closed when PHP finishes executing i.e. the page content has been produced. Then the function `tidy_entire_page` is called and passed a string containing all the HTML code of the resulting page.

`function tidy_entire_page($buffer) { ... }` is where the magic happens. It instantiates the PHP library to HTMLTidy and runs it over the HTML code, producing "tidied" code (following the rules defined in `src/header.php`).

Note that PHP's bindings to HTMLTidy aren't enabled as-standard, _but they can sometimes look as if they are_. PHP's a deceptive beast. Try to run this code without the 'tidy' extension properly configured and working and it won't throw an error... and it might even try to re-indent your code! But it won't run the full HTMLTidy suite. This will cause you some frustration, because it just won't seem to be working properly. If you examine `Dockerfile` you'll see the steps I took to ensure that the PHP-to-HTMLTidy bindings were properly set up in this container, but your real-life hosting may be different (see below).

(Fun fact: PHP has this same strange "Don't have the extension? I'mma try my hardest anyway!" behaviour with ZIP files. If you don't have the 'zip' extension installed, PHP _can still make ZIP files_, it's just they can't be _compressed_ ZIP files. PHP is a weird language.)

### Doing It For Real

To implement this in your own theme, on your own website:

1. Install a recent version of `libtidy`. If you're running a relatively up-to-date distro you can probably get this from your favourite package manager with e.g. `apt update && apt install -y libtidy-dev`. If you're not, then you might have to compile your own. If you get problems like HTMLTidy not believing that certain "modern" elements like `<video>` exist, check your `libtidy` version.
2. Install the PHP extension for HTMLTidy. Depending on how your PHP is built this might be as simple as `apt update && apt install -y php-tidy` or uncommenting a line in `php.ini`... or it might as complicated as having to recompile PHP but with the `--with-tidy` flag.
3. Test that HTMLTidy is installed by running `php -i | grep tidy`: if you get any output, that's probably great!
4. Edit your theme's `header.php` to include the code from `src/header.php` in this repository. Note that the `ob_start(...)` command needs to come before _any_ output, even the `<!DOCTYPE html>` declaration.
  - Alternatively, you might be able to get away with hooking into `init` from `functions.php` or even a plugin, but you run the risk of a higher-priority hook making you have a Very Bad Day. Up to you.
5. Reconfigure the HTMLTidy settings according to your preferences. You might find the [full list of options](https://api.html-tidy.org/tidy/quickref_next.html) helpful.
6. Implement any _exception_ rules you want to. E.g. you might opt to allow logged-in-users to _not_ tidy HTML content by sending a GET parameter `?skip-tidy=true`, e.g. with some code like this: `if( isset( $_GET['skip-tidy'] ) && is_user_logged_in() ) return $buffer;`
7. Make sure you've got some kind of caching set up. Seriously.

#### Caching?

HTMLTidy isn't the _most_ expensive operation, but you don't want to be doing it for every single load of every single page. Make sure your webserver's configured to microcache content, or else use a plugin like [WP Super Cache](https://wordpress.org/plugins/wp-super-cache/) _with the "advanced" configuration that allows your webserver to send pages to anonymous users without hitting PHP at all_.

An alternative approach might be to implement something that runs HTMLTidy on posts _as you save them_ (and then tidy up your `header.php`/`footer.php` etc. manually). That's not for me, but maybe that's the direction you want to go in.

#### Future

I do all kinds of other hacks in my `tidy_entire_page` function. Some of them... like running `preg_replace` on a HTML page... I'm not proud of (there's nothing like running an irregular language through a regular expression to make you feel bad about your life choices).

Others are fine, like stripping out `<link rel="stylesheet>` elements and replacing them with inline `<style>...</style>` blocks (my HTML + CSS is compact enough that for 95%+ of pages [I can fit both into a single <12kb block](https://danq.me/2023/11/04/fast-wordpress-the-hard-way/#21658-12kb), at which point it becomes more-efficient to bundle them into a single file and avoid the round-trip!). Your mileage may vary.

But personally, I'm really excited for the upcoming release of PHP 8.4's [modern `\Dom\Document` implementation](https://wiki.php.net/rfc/dom_additions_84), which will allow me to dynamically manipulate my output content using XPath rather than the hackiest-of-hacks. Until then, though, HTMLTidy's still a great step-up (if you're the kind of person who cares about how pretty their HTML is!).

## Thanks

Thanks to @edent for [encouraging](https://mastodon.social/@Edent/112871192888419993) me to share this.
