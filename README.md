# The Daily Bruin Classifieds Importer

Import Daily Bruin classified ads exported from AdPro in XML.

## Installation

1. Upload the classifieds folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress

## Changelog

### 0.5 #
* Initial release.

## Known Issues

*	Ads seem formatted for line breaks, but none are displayed on the current DB website (probably because they would take too much vertical space). The result is mashed-together words. To remedy this, the plugin adds spaces after each <paragraph> in the XML, but the text lacks punctuation. If possible, it should be made clear in AdPro (?) that no line returns should be used.
*	Currently, the plugin gives all tags the single category of "classified". A more complex taxonomy should be implemented for navigation and organization. 

