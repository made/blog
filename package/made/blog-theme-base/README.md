# made/blog-theme-base

The base theme for `made/blog`.

This one comes pre-installed with every new `made/blog` based installation.

This README is only a small summary of what will eventually be available in the [blog-documentation]() later.

## About

The theme is based on the bootstrap 4 blog template found [here](https://getbootstrap.com/docs/4.4/examples/blog/).

## Installation

The installation steps require [composer](https://getcomposer.org/) and [npm](https://www.npmjs.com/), as well as 
[gulp](https://gulpjs.com/) to be installed on your system.

### The package

If you want to use the theme as is, you can install the theme as if it were a composer package. Then link it to to the 
theme directory using a symbolic link.

#### On Linux/Mac

```bash
composer require made/blog-theme-base
ln -s ./vendor/made/blog-theme-base ./theme/blog-theme-base
```

Tip:    Use `composer setup` to avoid typing the link command.

#### On Windows

> By default windows does not support `ln` for link creation. You'l have to use `mklink` for that. Notice that the order
> of arguments is flipped compared to `ln`.

```bash
composer require made/blog-theme-base
mklink /D .\theme\blog-theme-base .\vendor\made\blog-theme-base
```

Tip:    Use `composer setup` to avoid typing the link command. But note that windows requires you to run that command with
        administrative rights.

#### Alternative method(s)

Alternatively you can always download current release and extract it into the `theme` directory. Make sure the contents
are inside a sub-folder, otherwise the theme will not be recognized.

You can also put the downloaded theme anywhere and only register it through a custom `theme.json` file, which has to be
placed inside a sub-folder of the `theme` directory. Please refer to the official documentation for details on how this
works.

### The assets

Now that you have the general package installed through composer or one of the other options, it's all about the assets.

#### Npm dependencies

Installing the npm dependencies is as easy as running the below command.

```bash
npm install
```

#### Gulp tasks

This theme uses gulp for it's build tasks. It relies on [this gulpfile](https://github.com/GameplayJDK/gulpfile) for all
its operations. That include:

| Asset Unit    | Feature                       |
| ------------- | ----------------------------- |
| Style         | Scss compilation              |
| Script        | Source concatenation          |
|               | Source minification           |
|               | Sourcemap generation          |
| Font          | Just copying                  |
| Image         | File minification             |
|               | Responsive format generation  |

To get a list of all available tasks, run `gulp tasks` inside of the `blog-theme-base` folder, which is located inside
the `theme` directory (as symlink or folder).

If you plan on modifying the source, it is recommended to fork the repository and add it as a
[vcs repository](https://getcomposer.org/doc/05-repositories.md#vcs) to the `composer.json`, or simply cloning the fork
into the `theme` directory. Of course downloading the current release and making the changes will also do the job, but 
will make upgrading a lot more complicated.

For initial installation running `gulp` will do everything needed. If you would like to use responsive images, you'll
have give `gulp image:responsive-compile` a call before that.

## Usage

To use this theme, there are two steps necessary.

1.  In your post configuration, set the `template` directive (inside of the post configuration locale node) to
    `@theme-base/` followed by the name of any of the top level template files.

2.  Make sure your controller class extends the controller class provided by this package. As this step is highly 
    dependent on your individual setup, you will have to figure this out on your own, most of the time.
    
    Alternatively you can simply call through on the provided controller from your own customized one.

## License

It's GPLv3.
