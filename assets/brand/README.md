# Пакет логотипов Amour и Eudora

Мастера из `amour_eudora_package.zip`. В Git лежат SVG и фавиконы; PDF для печати и превью PNG остаются только локально (`.gitignore`).

**Проверено 20.11.2026:** логотипы прототипа в `assets/images` геометрически совпадают с этими мастерами (одинаковые контуры). Прототип только убрал фон и перекрасил. В теме берем файлы пакета без фона (`*_transparent.svg`), цвет задаем токеном через `currentColor`. Набирать логотип шрифтом нельзя (CLAUDE.md).

| Где на сайте | Файл пакета | Цвет | Аналог в прототипе |
|---|---|---|---|
| Шапка на светлом фоне | `amour/2_lockups/lockup_horizontal_transparent.svg` | терракота | `logo-amour-horizontal.svg` |
| Шапка поверх фото и темных блоков, меню | тот же | известь | `logo-amour-horizontal-light.svg` |
| Логотип в футере | `amour/2_lockups/lockup_main_detailed_transparent.svg` | тамариск на вине | `logo-amour-main-light.svg` |
| Большое слово AMOUR внизу футера | буквы из `amour/2_lockups/wordmark_transparent.svg` (без подписи, рамка по буквам) | известь | `footer-wordmark-amour.svg` |
| AMOUR на первом экране главной | то же | терракота → известь при раскрытии | `home-hero-wordmark-amour.svg` |
| Шапка на страницах виллы | `eudora/2_lockups/lockup_horizontal_simplified_transparent.svg` | море / известь | `logo-eudora-horizontal.svg` |
| Большое слово EUDORA в футере виллы | `eudora/2_lockups/wordmark_nosub_light.svg` (буквы, рамка по буквам) | известь | `footer-wordmark-eudora.svg` |
| Поле олив в футере | `amour/1_mark/tree_3_pictogram/` | по токену | `pattern-olive-pictogram.svg` |
| Фавикон | `amour/5_favicon/`, у виллы — `eudora/5_favicon/` | — | — |

Файлы темы (`wp-content/themes/amour/assets/brand/`) собирает скрипт — он только убирает фон, ставит `currentColor` и обрезает рамку у больших слов, контуры не трогает. Запускать после обновления пакета:

```bash
python3 bin/build-brand.py
```

**Вес:** логотип шапки — 78 КБ в сжатом виде (контуры с очень частыми точками). Браузер скачивает его один раз; упростить контуры можно на неделе 6 (скорость), но это меняет мастер — только с согласия.

Узор `pattern-eudora-jug-field.svg` (футер виллы) нарисован на холсте, в пакете его нет — проверить при сборке виллы (неделя 3).
