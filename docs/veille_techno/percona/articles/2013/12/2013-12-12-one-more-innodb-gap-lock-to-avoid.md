---
title: One more InnoDB gap lock to avoid
source:
  name: Percona Blog
  url: https://www.percona.com/blog/one-more-innodb-gap-lock-to-avoid/
  post_id: 7603
source_author:
  name: Jervin Real
  slug: jervin
  url: https://www.percona.com/blog/author/jervin/
  website: ''
published_at: '2013-12-12T15:42:08'
published_at_gmt: '2013-12-12T15:42:08'
modified_at: '2026-04-28T21:58:44'
modified_at_gmt: '2026-04-28T21:58:44'
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
- Percona Services
category_slugs:
- insight-for-dbas
- mysql
- percona-services
tags:
- gap lock
- InnoDB
tag_slugs:
- gap-lock
- innodb
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# One more InnoDB gap lock to avoid

Source: [Percona Blog](https://www.percona.com/blog/one-more-innodb-gap-lock-to-avoid/)

Auteur source: [Jervin Real](https://www.percona.com/blog/author/jervin/)

Publication: 2013-12-12T15:42:08

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

While troubleshooting deadlocks for a customer, I came around an interesting situation involving InnoDB gap locks. For a non-INSERT write operation where the WHERE clause does not match any row, I expected there should’ve been no locks to be held by the transaction, but I was wrong. Let’s take a look at this table and … Continued

## Auteur source

As Senior Consultant, Jervin partners with Percona's customers on building reliable and highly performant MySQL infrastructures while also doing other fun stuff like watching cat videos on the internet. Jervin joined Percona in Apr 2010.
