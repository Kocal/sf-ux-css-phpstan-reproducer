# sf-ux-css-reproducer

Reproduces the workload from [phpstan/phpstan-src#6643](https://github.com/phpstan/phpstan-src/pull/6643): the `CssStyles` array shape that [symfony/ux-css](https://github.com/symfony/ux/pull/3933) (pinned at commit `3775f2ad20047c49fed7e798924d09f65e97a63e`) generates for its `css()` Twig function.

## Setup

`composer install` pulls `symfony/ux-css` from a Composer `package` repository pointing at the archive of that commit; the tokens live in `config/packages/ux_css.yaml`.

`config/reference_css.php` is written by the bundle when the container is built in debug mode (`bin/console cache:clear`): 847 keys, 144 of them (conditions/breakpoints like `_hover`, `md`) typed `array<string, mixed>`.

`config/reference_css_nested.php` is the same file with those 144 keys turned into a `CssStylesNested` shape, 2 levels deep. Regenerate it with `sh bin/nest-reference-css`.

The analysed code is `src/Ui/Button.php`: one method with a `CssStyles` parameter, passed to one call to `CssRuntime::css()`.

## Running

```
vendor/bin/phpstan analyse --debug -vvv                        # 1 level, phpstan.dist.neon
vendor/bin/phpstan analyse -c phpstan-nested.neon --debug -vvv # 2 levels
```

`--debug -vvv` prints the time spent on the file. To test the PR branch, clone [phpstan-src](https://github.com/phpstan/phpstan-src) next to this project, run `git fetch origin pull/6643/head:pr-6643 && git checkout pr-6643 && composer install` there, then from this project: `php ../phpstan-src/bin/phpstan analyse -c phpstan-nested.neon --debug -vvv`.

## Results

Time spent analysing `src/Ui/Button.php` (PHP 8.4, macOS, `XDEBUG_MODE=off`, phpstan-src run from source, no `phpstan_turbo` extension):

| | 1 level | 2 levels |
|---|---|---|
| phpstan/phpstan 2.2.16 (phar) | 2.6 s | 64 s |
| phpstan-src 2.3.x @ `261a431e` (base of the PR) | 0.58 s | 22.6 s |
| phpstan-src #6643 @ `c0c6b05` | 0.58 s | 21.0 s |
