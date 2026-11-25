#!/usr/bin/env bash
# Template strings of the theme (and the few of the plugin a guest sees) → languages/amour.pot, then the German
# file is brought up to date and compiled. Run after changing a string in a template:  npm run i18n
# New strings come out untranslated in languages/de_DE.po: translate them there and run it again.
set -euo pipefail
run() { npx wp-env run cli "$@"; }
T=wp-content/themes/amour
run wp i18n make-pot wp-content/plugins/amour-core /tmp/amour-plugin.pot --domain=amour --include=inc/contact-form.php --skip-js --skip-block-json
run wp i18n make-pot "$T" "$T/languages/amour.pot" --domain=amour --skip-js --skip-block-json --skip-theme-json --exclude=page-styleguide.php --merge=/tmp/amour-plugin.pot
run wp i18n update-po "$T/languages/amour.pot" "$T/languages/de_DE.po"
run wp i18n make-mo "$T/languages/de_DE.po"
run wp i18n make-php "$T/languages/de_DE.po"
