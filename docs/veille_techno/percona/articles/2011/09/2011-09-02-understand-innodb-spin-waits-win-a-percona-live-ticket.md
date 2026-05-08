---
title: Understand InnoDB spin waits, win a Percona Live ticket
source:
  name: Percona Blog
  url: https://www.percona.com/blog/understand-innodb-spin-waits-win-a-percona-live-ticket/
  post_id: 3046
source_author:
  name: Baron Schwartz
  slug: baron
  url: https://www.percona.com/blog/author/baron/
  website: http://www.percona.com/
published_at: '2011-09-02T13:55:17'
published_at_gmt: '2011-09-02T13:55:17'
modified_at: '2026-04-28T21:28:18'
modified_at_gmt: '2026-04-28T21:28:18'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
matched_filters:
- category:mysql:83
categories:
- Insight for DBAs
- MySQL
- Percona Events
category_slugs:
- insight-for-dbas
- mysql
- percona-events
tags: []
tag_slugs: []
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Understand InnoDB spin waits, win a Percona Live ticket

Source: [Percona Blog](https://www.percona.com/blog/understand-innodb-spin-waits-win-a-percona-live-ticket/)

Auteur source: [Baron Schwartz](https://www.percona.com/blog/author/baron/)

Publication: 2011-09-02T13:55:17

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

It’s Friday again (so soon!) and time for our TGIF contest, to give away a free ticket to Percona Live London. Before we do that, though, just what in the world does this output from SHOW INNODB STATUS mean? Mutex spin waits 5870888, rounds 19812448, OS waits 375285 1 Mutex spin waits 5870888 , rounds 19812448 , OS waits 375285 To understand this text, you have to understand how InnoDB handles mutexes. It tries a … Continued

## Auteur source

Baron is the lead author of High Performance MySQL. He is a former Percona employee.
