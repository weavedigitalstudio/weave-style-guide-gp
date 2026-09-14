# Weave Style Guide for GeneratePress

An auto-generated style guide page for our GeneratePress and Beaver Builder sites. It reads the live site when the page loads, so nothing on it is typed in and it can't drift from the Customizer.

It's the scaled-down sibling of [weave-style-guide](https://github.com/weavedigitalstudio/weave-style-guide), which does the same job for block themes. There are no patterns, block styles or spacing scale here, because GP and Beaver Builder sites don't keep those anywhere readable.

Sections, in page order: intro, logo, colours, fonts, icons, forms, contrast.

It follows the seventeen Weave colour names: contract 0.2.1 in `weave-blocks/docs/scaffold/boilerplate-tokens.json`, which `weave-playbook/sops/40-design/weave-figma-boilerplate.md` ("Colour names" and "Naming the extras") and build-15 follow (`surface`, `surface-inverse`, `text`, `primary`, `accent` and the rest). A site whose GP palette has at least `surface` and `text` gets colours grouped by job, logos on its `surface` colour and a required pairings check. Live sites keep the slugs they launched with, so older sites without those names get the simpler layout and the plugin works on both.

## Install

Install from the latest release zip, then create the page:

```
wp plugin install https://github.com/weavedigitalstudio/weave-style-guide-gp/releases/latest/download/weave-style-guide-gp.zip --activate
wp weave-style-guide-gp create                # creates /style-guide/ if missing
wp weave-style-guide-gp create --refresh      # rewrites the page content
wp weave-style-guide-gp create --slug=brand   # a different address
```

On GridPane, WP-CLI runs through `gp wp` with the site's domain in front of the command:

```
gp wp <site.url> plugin install https://github.com/weavedigitalstudio/weave-style-guide-gp/releases/latest/download/weave-style-guide-gp.zip --activate
gp wp <site.url> weave-style-guide-gp create
```

Locally in WordPress Studio it's `studio wp --path ~/Studio/<site> weave-style-guide-gp create`.

Without a terminal, use the link under the plugin's name on the Plugins screen. It reads **Create style guide page** until the page exists, then **View style guide** (or **Edit style guide page** while it isn't published). There's nothing to re-scan: the page reads the site every time it loads.

It won't touch a page it didn't create. Plenty of our older sites have a hand-built `/styles/` page, so the default address is `/style-guide/`.

Or through the Abilities API when weave-abilities is active: `weave/style-guide-create` with `{ "slug": "style-guide", "refresh": true }`. It's the same ability the block-theme plugin registers, so one sweep covers both kinds of site. Only activate one of the two plugins on a site.

The page is one shortcode. `[weave_style_guide]` renders the whole guide. `[weave_style_guide section="colours"]` renders one section (a comma list for several), so a section can sit in a Beaver Builder layout. `form="3"` picks the Gravity Form.

## What each section reads

| Section | Source |
|---|---|
| intro | One line saying the page is read live, plus a note when the site uses the Weave colour names |
| logo | Customizer logo slots: Site Identity logo, GP retina logo, GP Premium mobile header and sticky navigation logos. Each on white and on the site's background colour (`surface` on sites with the Weave colour names, otherwise GP's Customizer background colour), or on one panel when that background is white anyway, with a download link. An image used in two slots shows once. Site icon at 96 to 16 pixels |
| colours | GP global colours with their names: swatch, hex, RGB, HSL and `var(--slug)`, click to copy. With the Weave colour names: colour jobs first (each with its job, and the colour it points at when it's a `var(--other)` reference), then other colours (named for their job, with the brand's swatch name as the label), then a note listing any GeneratePress starter colours (`contrast`, `base` and their numbered versions) still in the palette, with the GP settings that point at each. Without them: main colours, with tints named after a main colour grouped under it. Copy the palette as CSS or JSON |
| fonts | GP Premium Font Library families (the Font Manager on sites without GP Premium). Real headings and body text with their settings and live size. A table of every Customize > Typography setting. Flags Beaver Builder Global Styles type when it's set |
| icons | Icon sets uploaded to Beaver Builder (IcoMoon or Fontello zip) and enabled. Click an icon to copy its class |
| forms | The shortest Gravity Form (fewest fields), styled by the site. Long multi-step forms can pop save-progress prompts, so they're skipped unless `form="3"` asks for one |
| contrast | With the Weave colour names: the required pairings from the boilerplate (each surface with its text colour, `text-inverse` on `primary` and `accent`), pass or fail at 4.5:1. Then WCAG 2 ratios for every palette pairing, AA and AAA marked |

## Setting a site up for it

- Fill Logo and Site Icon under Appearance > Customize > Site Identity, even when a Themer header shows the logo. Themer replaces the GP header, so nothing shows twice.
- On new builds, use the seventeen Weave colour names as the GP global colour slugs. Name any extra colour for its job (`on-dark`, not `teal-500`) and put the brand's swatch name in the label. Older palettes: name tints after their main colour (`brand-green-light`, `brand-green-dark`) and they group under it.
- The page's own borders, muted text and card backgrounds use `--border`, `--text-muted`, `--surface` and `--surface-subtle` when the site has them, and neutral greys when it doesn't.
- Upload the icon set under Settings > Beaver Builder > Icons and enable it.

## Shared with weave-style-guide

`inc/colour.php`, `inc/github-updater.php`, `assets/copy.js` and most of `assets/style.css` started as copies. A fix to one probably belongs in the other.

## Notes

- The page gets a noindex meta tag and GP's no-sidebar layout, so the grids use the whole container.
- The guide prints the page title as its H1 and switches off GP's content title on that page. Some of our Beaver Builder sites hide GP titles (Value does), and a site that shows them would otherwise get two. Run `create` again on an existing guide page to switch GP's title off.
- The page's own styling is neutral on purpose. The site's colours, fonts and icons are the only brand on it.
- The GitHub updater looks for releases on `weavedigitalstudio/weave-style-guide-gp`. Tag `v*` to build a release zip.
