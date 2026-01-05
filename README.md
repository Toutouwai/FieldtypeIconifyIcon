# Iconify Icon

A bundle of fieldtype, inputfield, and admin helper modules for searching and displaying [Iconify](https://icon-sets.iconify.design/) icons. Over 200,000 open source vector icons are available for selection.

Requires the FileValidatorSvgSanitizer module.

Be sure to abide by the license terms of any icons you use. The license of each icon set is viewable on the [Iconify](https://icon-sets.iconify.design/) website.

![Screencast](https://github.com/user-attachments/assets/369778af-2bca-4107-9f98-4feb0bc70e7e)

# Fieldtype and inputfield modules

When the FieldtypeIconifyIcon and InputfieldIconifyIcon modules are installed you can create a field of type IconifyIcon.

## Field config options

**Iconify icon set prefixes:** In most cases you will want to define one or more icon set prefixes for the field, to limit the search to those particular icon sets. This is because the number of icons available through Iconify vastly exceeds the maximum of 999 results that can be returned via the Iconify search API.

You can find the prefix of an icon set from its URL by browsing at [https://icon-sets.iconify.design/](https://icon-sets.iconify.design/). For example, the prefix of the icon set browsable at https://icon-sets.iconify.design/mdi/ is "mdi".

Enter the icon set prefixes into the config field separated by commas.

**Icon preview size:** Enter a width/height in pixels for the preview of the selected icon if you want to override the default.

## Using the inputfield

Type an icon name (or part of an icon name) into the search input and a list of matching icons will be displayed. You can hover on an icon in the results to see the set prefix and name of the icon. Click on an icon to select it.

If you have not defined any icon set prefixes in the field config then you can limit the search to particular icon sets by entering icon set prefixes into the search input before a colon. For example, entering "mingcute,tabler:flower" would search for icons with "flower" in their name from the "mingcute" and "tabler" icon sets.

When the page is saved the selected icon is downloaded from Iconify, sanitized via the FileValidatorSvgSanitizer module, and stored within the `/site/assets/iconify/` directory. Icons are not automatically deleted from this directory if they are no longer used in a page value, but if you want to clean up this directory at any point you can delete it and icons will be automatically re-downloaded when they are next needed.

## The field value

The formatted value of a IconifyIcon field is a WireData object with the following properties:

- set: The icon set prefix
- name: The icon name
- path: The path to the icon file
- url: The URL to the icon file
- svg: The SVG code of the icon
- raw: The raw icon value that is stored in the database

For example, if your icon field was named "icon" and you were outputting the `src` attribute of an `<img>` tag, you would use `$page->icon->url`. Or if you were outputting inline SVG code you would use `$page->icon->svg`.

The unformatted value of a IconifyIcon field is the raw database value. Normally you won't need to deal with the raw value when using the inputfield, but if you want to use the API to set a field value then the format of the raw value is `iconify--[icon set prefix]--[icon name]`. Example: `iconify--mingcute--flower-line`.

Example of object properties:

![Object properties](https://github.com/user-attachments/assets/a020a281-c09f-4700-8d4e-d068ac817a86)

# Using Iconify icons in the ProcessWire admin

Installing the AdminIconifyIcon module allows you to use Iconify icons as field, template or page icons in the ProcessWire admin. Icons used in the ProcessWire admin are monochrome so any colours or shades in selected icons will not be preserved. 

## Module config

You can define icon set prefixes and the icon preview size in the module config. These settings are applied to the inputfields used to set Iconify icons for fields and templates.

## Field and template icons

An "Iconify icon" field is added to the Edit Field and Edit Template screens. When this field is populated it overrides any selection in the core "Icon" field and this field is hidden.

## Page icons

To use an Iconify icon as a page icon for admin pages in the ProcessWire menus, create a IconifyIcon field named "page_icon" and add it to the "admin" system template. For any page using the admin template (e.g. a page representing a Lister Pro instance), open it in Page Edit and select an icon in the "page_icon" field.

An example of a "Countries" Lister Pro instance with an Iconify icon:

![Page icon](https://github.com/user-attachments/assets/f48ab78d-4d21-4262-b3c2-af72bb97f01c)
