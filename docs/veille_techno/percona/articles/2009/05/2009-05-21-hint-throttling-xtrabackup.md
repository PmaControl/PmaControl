---
title: 'Hint: throttling xtrabackup'
source:
  name: Percona Blog
  url: https://www.percona.com/blog/hint-throttling-xtrabackup/
  post_id: 1918
source_author:
  name: Vadim Tkachenko
  slug: vadim
  url: https://www.percona.com/blog/author/vadim/
  website: http://www.percona.com/
published_at: '2009-05-21T06:21:00'
published_at_gmt: '2009-05-21T06:21:00'
modified_at: '2026-03-23T21:27:42'
modified_at_gmt: '2026-03-23T21:27:42'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- XtraBackup
matched_filters:
- search:xtrabackup
categories:
- Percona Software
category_slugs:
- percona-software
tags:
- Backups
- Tips
tag_slugs:
- backups
- tips
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Hint: throttling xtrabackup

Source: [Percona Blog](https://www.percona.com/blog/hint-throttling-xtrabackup/)

Auteur source: [Vadim Tkachenko](https://www.percona.com/blog/author/vadim/)

Publication: 2009-05-21T06:21:00

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Using xtrabackup for copying files can really saturate your disks, and that why we made special option --throttle=rate 1 -- throttle = rate to limit rate of IO per second. But it really works when you do local copy.What about stream backup ? Even you copy just to remote box with innobackupex --stream=tar | ssh remotebox "tar xfi -" 1 innobackupex -- stream = tar | ssh remotebox "tar xfi -" , read may be so intensive so your mysqld … Continued

## Auteur source

Vadim Tkachenko co-founded Percona in 2006 and leads Percona Labs, which focuses on technology research and performance evaluations of Percona’s and third-party products. Vadim’s expertise in LAMP performance and multi-threaded programming help optimize MySQL and InnoDB internals to take full advantage of modern hardware. He also co-authored the book High Performance MySQL: Optimization, Backups, and Replication 3rd Edition.
