# 📰 WPArticle-MSNSlideshow-Plugin (Feed Send)

**Feed Send** is a WordPress plugin designed to send articles and slideshows to a backend server, facilitating the creation of dynamic RSS feeds. This enables your content to appear on multiple sites supporting RSS, including platforms like Microsoft Start. **It is intended to be used in conjunction with the WPArticle-MSNSlideshow Backend Server.**

---

## 🚀 Overview

Feed Send integrates with WordPress posts and provides options to send post content as slideshows or basic feeds to a server via HTTP requests. The plugin includes a meta box for each post, allowing users to select whether to send the post as a slideshow or basic feed.

### Core Features

- Adds a custom meta box in the WordPress post editor with options:
  - MSN Slideshow
  - MSN Basic
- Sends selected post data to a backend server via JSON `POST` request
- Captures featured image, post content, author name, and scheduled post date
- Supports deleting a post from the feed when the option is unchecked
- Works with dynamic feed backends for RSS aggregation

---

## ⚙️ Installation

1. Upload the plugin folder to `/wp-content/plugins/feed-send/`
2. Activate the plugin via the WordPress admin panel
3. Edit a post and configure the MSN Slideshow or MSN Basic options in the meta box

---

## 🖼️ Meta Box Options

The plugin adds a meta box titled **MSN Options** to posts, including the following fields:

| Option           | Description |
|-----------------|-------------|
| MSN Slideshow    | Sends the post content as a slideshow to the backend feed |
| MSN Basic        | Sends the post content as a basic feed item |

Check or uncheck the options to send or remove the post from the feed.

---

## 📡 Data Sent to Backend

When a post is saved, the following JSON payload is sent via HTTP POST to the backend server:

```json
{
  "content": {
    "featured_image": "<featured_image_url>",
    "featured_image_credit": "<featured_image_caption>",
    "post_title": "Post Title",
    "post_link": "Post URL",
    "post_content": "Full post content HTML",
    "author_name": "Author Display Name",
    "post_date": "Scheduled date or N/a",
    "type": "slideshow or basic",
    "GUID": "WordPress post ID",
    "delete": true/false
  }
}
```

### Notes
- If the post is scheduled (`future` status), the scheduled date is sent in GMT.
- `delete: true` indicates the post should be removed from the feed.

---

## 🔧 Functions

| Function | Description |
|----------|-------------|
| `send_request($post_id, $type, $delete = false)` | Sends post data to the backend server as JSON. Handles slideshow creation and deletion. |
| `get_featured_image_url($post_id)` | Returns the URL of the post's featured image. |
| `get_featured_image_caption($post_id)` | Returns the caption of the post's featured image. |
| `my_custom_meta_box()` | Adds the MSN Options meta box to the post editor. |
| `my_meta_box_callback($post)` | Renders the meta box HTML with checkboxes. |
| `save_my_meta_box_data($post_id)` | Handles saving post meta and sending requests to backend on post save. |

---

## ⚙️ Setup Requirements

- Ensure your backend server is running and accessible at the URL configured in `send_request()` (`https://api.pluginpioneers.tech/req`).
- Posts must have a featured image if you want it included in the feed.
- Proper user permissions are required to save posts and meta fields.
- **This plugin must be used in conjunction with the WPArticle-MSNSlideshow Backend Server to function properly.**

---

## 📄 License

MIT License — Use and modify freely.

