---
title: Why you should ignore MySQL’s key cache hit ratio
source:
  name: Percona Blog
  url: https://www.percona.com/blog/why-you-should-ignore-mysqls-key-cache-hit-ratio/
  post_id: 2238
source_author:
  name: Baron Schwartz
  slug: baron
  url: https://www.percona.com/blog/author/baron/
  website: http://www.percona.com/
published_at: '2010-02-28T17:34:45'
published_at_gmt: '2010-02-28T17:34:45'
modified_at: '2026-04-28T21:07:29'
modified_at_gmt: '2026-04-28T21:07:29'
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
category_slugs:
- insight-for-dbas
- mysql
tags:
- key_buffer
- key_buffer_size
- MyISAM
- MyISAM Key Cache
tag_slugs:
- key_buffer
- key_buffer_size
- myisam
- myisam-key-cache
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Why you should ignore MySQL’s key cache hit ratio

Source: [Percona Blog](https://www.percona.com/blog/why-you-should-ignore-mysqls-key-cache-hit-ratio/)

Auteur source: [Baron Schwartz](https://www.percona.com/blog/author/baron/)

Publication: 2010-02-28T17:34:45

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

I have not caused a fist fight in a while, so it’s time to take off the gloves. I claim that somewhere around of 99% of advice about tuning MySQL’s key cache hit ratio is wrong, even when you hear it from experts. There are two major problems with the key buffer hit ratio, and … Continued

## Structure detectee

- H3: The key_buffer hit ratio
- H3: Problem 1: Ratios don’t show magnitude
- H3: Problem 2: Counters don’t measure time
- H3: A partially valid use of Key_reads
- H3: How to choose a key_buffer_size
- H3: What about InnoDB tuning?
- H3: Summary

## Auteur source

Baron is the lead author of High Performance MySQL. He is a former Percona employee.
