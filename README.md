# slickstream-wp-plugin
Slickstream WordPress Plugin

The `slick-engagement` folder within this project is the root folder for a WordPress plugin that is used to integrate Slickstream's engagement widgets.

To create the plugin, zip the `slick-engagement` folder into `slick-engagement.zip`. This ZIP can then be uploaded to WordPress.

## Local development

Run with [`wp-env`](https://developer.wordpress.org/block-editor/reference-guides/packages/packages-env/):

```sh
source ~/.zshrc && npx @wordpress/env start
source ~/.zshrc && npx @wordpress/env run cli wp plugin activate slick-engagement
source ~/.zshrc && npx @wordpress/env run cli wp theme activate twentytwentyone
```

Open `http://localhost:8888/wp-admin` and log in with `admin` / `password`.

Plugin settings: `http://localhost:8888/wp-admin/options-general.php?page=SlickstreamSettings`

Stop locally:

```sh
source ~/.zshrc && npx @wordpress/env stop
```
