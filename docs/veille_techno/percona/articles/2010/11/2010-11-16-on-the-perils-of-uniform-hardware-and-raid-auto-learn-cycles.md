---
title: The perils of uniform hardware and RAID auto-learn cycles
source:
  name: Percona Blog
  url: https://www.percona.com/blog/on-the-perils-of-uniform-hardware-and-raid-auto-learn-cycles/
  post_id: 2503
source_author:
  name: Baron Schwartz
  slug: baron
  url: https://www.percona.com/blog/author/baron/
  website: http://www.percona.com/
published_at: '2010-11-16T16:40:56'
published_at_gmt: '2010-11-16T16:40:56'
modified_at: '2026-03-23T21:47:11'
modified_at_gmt: '2026-03-23T21:47:11'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
matched_filters:
- category:mysql:83
categories:
- Hardware and Storage
- MySQL
category_slugs:
- hardware-and-storage
- mysql
tags:
- AutoLearn
- Dell
- MegaCLI
- MegaRAID
- PERC
tag_slugs:
- autolearn
- dell
- megacli
- megaraid
- perc
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# The perils of uniform hardware and RAID auto-learn cycles

Source: [Percona Blog](https://www.percona.com/blog/on-the-perils-of-uniform-hardware-and-raid-auto-learn-cycles/)

Auteur source: [Baron Schwartz](https://www.percona.com/blog/author/baron/)

Publication: 2010-11-16T16:40:56

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Last night a customer had an emergency in selected machines on a large cluster of quite uniform database servers. Some of the servers were slowing down in a very puzzling way over a short time span (a couple of hours). Queries were taking multiple seconds to execute instead of being practically instantaneous. But nothing seemed … Continued

## Auteur source

Baron is the lead author of High Performance MySQL. He is a former Percona employee.
