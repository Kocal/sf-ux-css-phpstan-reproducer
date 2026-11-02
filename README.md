# sf-ux-css-reproducer

Reproduces the workload from [phpstan/phpstan#15348](https://github.com/phpstan/phpstan/issues/15348), first reported in [phpstan/phpstan-src#6643](https://github.com/phpstan/phpstan-src/pull/6643): the `CssStyles` array shape that symfony/ux-css generates for its `css()` Twig function. The package comes from the `3.x` branch of a local symfony/ux checkout, where it was merged with [symfony/ux PR #3933](https://github.com/symfony/ux/pull/3933).

## Setup

`composer install` symlinks `symfony/ux-css` through a Composer `path` repository pointing at `../../symfony/ux/src/Css`. symfony/ux must therefore be cloned at `../../symfony/ux` relative to this project, on the `3.x` branch. The tokens live in `config/packages/ux_css.yaml`.

`config/reference_css.php` is written by the bundle when the container is built in debug mode (`bin/console cache:clear`). Its `CssStyles` shape has 847 keys, each value typed with a union of literals (`AlignContentValue`, `SpacingToken`, ...). The 144 condition and breakpoint keys (`_hover`, `md`, ...) are typed `CssStylesNested`: the same 847 keys one level down, where those 144 keys are typed `array<string, mixed>`.

The analysed code is `src/Ui/Button.php`: one method with a `CssStyles` parameter, passed to one call to `CssRuntime::css()`.

## Running

```
vendor/bin/phpstan analyse --debug -vvv
```

`--debug -vvv` prints the time spent on the file.

To run another release or the latest dev build, install `phpstan/phpstan` at that version (e.g. `2.3.x-dev`) with Composer in a separate directory, then run its `vendor/bin/phpstan` from this project.

To run phpstan-src from source, run `composer install` in a [phpstan-src](https://github.com/phpstan/phpstan-src) clone, then `php <phpstan-src>/bin/phpstan analyse` from this project.

## Results

Time spent analysing `src/Ui/Button.php`, min-max over 5 runs after a discarded warm-up run (PHP 8.4.27, macOS arm64 on AC power, `XDEBUG_MODE=off`, 2026-11-02). Every run reports no errors.

| | Time |
|---|---|
| phpstan/phpstan 2.2.16 | 66.9-67.6 s |
| phpstan/phpstan 2.3.4 | 3.59-3.66 s |
| phpstan/phpstan 2.3.x-dev @ `0749d19` | 1.76-1.84 s |

Three PRs merged after 2.3.4 cut the time in half. Each one was measured from source against the commit just before it:

| PR | Before | After |
|---|---|---|
| [phpstan/phpstan-src#6672](https://github.com/phpstan/phpstan-src/pull/6672) | 3.66-3.83 s | 3.26-3.36 s |
| [phpstan/phpstan-src#6673](https://github.com/phpstan/phpstan-src/pull/6673) | 3.25-3.36 s | 2.92-2.98 s |
| [phpstan/phpstan-src#6671](https://github.com/phpstan/phpstan-src/pull/6671) | 2.89-2.96 s | 1.84-1.94 s |
