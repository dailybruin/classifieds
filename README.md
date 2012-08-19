# The Daily Bruin Classifieds Importer

Import Daily Bruin classified ads exported from AdPro in XML.

## Installation

1. Upload the classifieds folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress

## Usage

Use the new top-level Classifieds menu edit to individual ads or upload new ones.

Mark ads with the 'Featured' category to display them on the front page.

## Changelog

### 0.7
* Classification is now saved
* Unused classifications are deleted

### 0.6
* Featured ads are working
* A few mild bugs fixed

### 0.5 #
* Initial release.

## Known Issues

*	Ads seem formatted for line breaks, but none are displayed on the current DB website (probably because they would take too much vertical space). The result is mashed-together words. To remedy this, the plugin adds spaces after each <paragraph> in the XML, but the text lacks punctuation. If possible, it should be made clear in AdPro (?) that no line returns should be used. 

