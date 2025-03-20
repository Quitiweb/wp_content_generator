
# WP Content Generator

Generate WordPress posts automatically with AI-powered content generation. Perfect for quickly populating your WordPress site with quality content.

## Description

WP Content Generator is a powerful WordPress plugin that connects to a content generation API to automatically create posts. It's particularly useful for:

- Creating multiple posts quickly with AI-generated content
- Populating test environments with realistic content
- Generating Amazon affiliate content using ASINs
- Bulk content creation with category-specific posts

### Key Features

- Generate single or multiple posts at once
- Specify date ranges for post publication
- Select categories for generated content
- Support for Amazon product content generation using ASINs
- Easy-to-use interface in WordPress admin
- API key management for secure content generation

## Installation

1. Upload the `wp_content_generator` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to WP Content Generator settings to configure your API URL and key
4. Start generating content!

## Configuration

1. Navigate to WP Content Generator settings in your WordPress admin panel
2. Enter your Post Generator API URL
3. Input your API key
4. Save your settings

## Usage

### Standard Post Generation
1. Go to WP Content Generator > Generate Posts
2. Select your desired category
3. Choose date range for posts
4. Enter number of posts to generate
5. Click "Generate Posts"

### Amazon Product Posts
1. Go to WP Content Generator > Generate AWS Posts
2. Select categories
3. Enter Amazon ASINs (separated by spaces)
4. Choose date range
5. Click "Generate AWS Posts"

## Requirements

- WordPress 5.0 or higher
- PHP 7.2 or higher
- Valid API key for the Post Generator API

## Frequently Asked Questions

### How do I get an API key?
Contact your administrator or visit the API provider's website to obtain an API key.

### Can I generate posts with specific categories?
Yes, you can select categories for your generated posts in the generation form.

### Is there a limit to how many posts I can generate?
The current limit is 500 posts per batch.

## Changelog

### 1.0.1
- Added support for multiple ASIN processing
- Improved error handling and user feedback
- Added date range selection for post generation

### 1.0.0
- Initial release

## Support

For support questions, please create an issue in this repository or contact your administrator.

## License

This plugin is licensed under the GPL v2 or later.

