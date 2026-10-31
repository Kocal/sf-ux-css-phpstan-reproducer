# sf-ux-css-reproducer

Reproduces the workload from [phpstan/phpstan#15348](https://github.com/phpstan/phpstan/issues/15348), first reported in [phpstan/phpstan-src#6643](https://github.com/phpstan/phpstan-src/pull/6643): the `CssStyles` array shape that [symfony/ux-css](https://github.com/symfony/ux/pull/3933) generates for its `css()` Twig function. The package comes from the `feat/ux-css` branch of [symfony/ux PR #3933](https://github.com/symfony/ux/pull/3933), in a local symfony/ux checkout.

## Setup

`composer install` symlinks `symfony/ux-css` through a Composer `path` repository pointing at `../../symfony/ux/src/Css`. symfony/ux must therefore be cloned at `../../symfony/ux` relative to this project, with the PR checked out there (`gh pr checkout 3933`). The tokens live in `config/packages/ux_css.yaml`.

`config/reference_css.php` is written by the bundle when the container is built in debug mode (`bin/console cache:clear`): 847 keys, 144 of them (conditions/breakpoints like `_hover`, `md`) typed `array<string, mixed>`.

`config/reference_css_nested.php` is the same file with those 144 keys turned into a `CssStylesNested` shape, 2 levels deep. Regenerate it with `sh bin/nest-reference-css`.

The analysed code is `src/Ui/Button.php`: one method with a `CssStyles` parameter, passed to one call to `CssRuntime::css()`.

## Running

```
vendor/bin/phpstan analyse --debug -vvv                        # 1 level, phpstan.dist.neon
vendor/bin/phpstan analyse -c phpstan-nested.neon --debug -vvv # 2 levels
```

`--debug -vvv` prints the time spent on the file.

To run the latest dev build with the `phpstan_turbo` extension, install `phpstan/phpstan:2.3.x-dev` with Composer in a separate directory and run its `vendor/bin/phpstan` from this project.

To run phpstan-src from source (no turbo extension), run `composer install` in a [phpstan-src](https://github.com/phpstan/phpstan-src) clone, then `php <phpstan-src>/bin/phpstan analyse` from this project.

## Results

Time spent analysing `src/Ui/Button.php`, min-max over 5 runs after a discarded warm-up run (PHP 8.4.27, macOS arm64 on AC power, `XDEBUG_MODE=off`). Every run reports no errors.

| | 1 level | 2 levels |
|---|---|---|
| phpstan/phpstan 2.2.16 | 4.13-4.28 s | 66.2-66.6 s |
| phpstan/phpstan 2.3.x-dev @ `091c5a7`, with `phpstan_turbo` | 0.51-0.54 s | 3.55-3.58 s |
| phpstan-src 2.3.x @ `091c5a7`, from source, no turbo | 0.54-0.65 s | 3.65-3.71 s |

The turbo extension barely changes these numbers, the gain comes from the fixes in PHPStan 2.3.
