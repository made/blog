# Xdebug integration

To control xdebug specific cookies, use the [Bookmarklet generator](https://www.jetbrains.com/phpstorm/marklets/).

The configuration needed for xdebug to work locally (inside `php.ini`):

```ini
zend_extension=xdebug

[xdebug]
xdebug.remote_enable=1
;xdebug.remote_connect_back=true
xdebug.remote_host=127.0.0.1
xdebug.remote_port=9000
xdebug.idekey=PHPSTORM
```

The run configuration should be similar to the following:

1.  `Run` (PHP Built-in Web Server):

> Host: `made-blog.local` Port: `80`
> \
> Document root: `made-blog/public`
> \
> [x] Use router script: `made-blog/public/index.php`

2.  `Run (Profiler)` (PHP Built-in Web Server):

> Host: `made-blog.local` Port: `80`
> \
> Document root: `made-blog/public`
> \
> [x] Use router script: `made-blog/public/index.php`
>
> Interpreter options:
> ```bash
> --define
> xdebug.profiler_enable=0
> --define
> xdebug.profiler_enable_trigger=1
> --define
> xdebug.profiler_output_dir="F:\PhpstormProjects\made-blog\debug\snapshot"
> --define
> xdebug.profiler_output_name="xdebug.out.%t"
> ```

3. `Debug` (PHP Remote Debug):

> Server: `made-blog.local` (xdebug)
> \
> IDE key(session id): `PHPSTORM`
