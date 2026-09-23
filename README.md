# Custom Elementor OTR Widget

Elementor widget for displaying Old Time Radio episode tables with year tabs, episode metadata, individual downloads, scheduled episodes, and batch downloads.

## Version 2.7.8

Elementor 4.3 compatibility maintenance release.

### Changes

- Declares Elementor as a WordPress plugin dependency.
- Declares compatibility tested through Elementor 4.3.0 and Elementor Pro 4.3.0.
- Replaces deprecated `elementor/widgets/widgets_registered` with `elementor/widgets/register`.
- Replaces deprecated `register_widget_type()` with the current widget manager `register()` API.
- Replaces deprecated `_register_controls()` with `register_controls()`.
- Registers widget JavaScript and CSS as Elementor dependencies instead of globally enqueueing them on every front-end page.
- Preserves year tabs, category selection, published/future episodes, scheduled-release notices, episode duration/file size, individual downloads, and batch downloads.
- Adds safer output handling for generated download links.
- Keeps the existing GitHub release updater and automatic plugin-folder normalization.

## Updating

The plugin checks the latest published GitHub Release and integrates with the normal WordPress plugin update system. Publish a GitHub release whose tag matches the plugin version to distribute an update.
