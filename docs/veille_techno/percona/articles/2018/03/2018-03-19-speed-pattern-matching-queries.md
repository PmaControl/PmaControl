---
title: How to Speed Up Pattern Matching Queries
source:
  name: Percona Blog
  url: https://www.percona.com/blog/speed-pattern-matching-queries/
  post_id: 18212
source_author:
  name: Tibor Korocz
  slug: tibor-koroczpercona-com
  url: https://www.percona.com/blog/author/tibor-koroczpercona-com/
  website: ''
published_at: '2018-03-19T23:07:12'
published_at_gmt: '2018-03-19T23:07:12'
modified_at: '2026-05-05T19:06:17'
modified_at_gmt: '2026-05-05T19:06:17'
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
- MySQL
tag_slugs:
- mysql
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/pattern-matching-queries-e1521500722284.jpg
image_count: 2
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# How to Speed Up Pattern Matching Queries

Source: [Percona Blog](https://www.percona.com/blog/speed-pattern-matching-queries/)

Auteur source: [Tibor Korocz](https://www.percona.com/blog/author/tibor-koroczpercona-com/)

Publication: 2018-03-19T23:07:12

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

From time to time I see pattern matching queries with conditions that look like this: “where fieldname like ‘%something%’ “. MySQL cannot use indexes for these kinds of queries, which means it has to do a table scan every single time. (That’s really only half true — there are the FullText indexes. In another blog … Continued

## Structure detectee

- H4: But how is this useful?
- H4: Trigram table
- H4: Shorting method
- H4: Selectivity
- H4: Table statistics
- H4: Cons
- H4: Pros
- H4: Conclusion

## Images et graphiques reperes

- featured / image: [How to Speed Up Pattern Matching Queries](https://www.percona.com/wp-content/uploads/2026/03/pattern-matching-queries-e1521500722284.jpg)
- content / image: [pattern matching queries](https://www.percona.com/wp-content/uploads/2026/03/pattern-matching-queries-300x200.jpg)

## Auteur source

Tibi joined Percona in 2015 as a Consultant. Before joining Percona, among many other things, he worked at the world’s largest car hire booking service as a Senior Database Engineer. He enjoys trying and working with the latest technologies and applications which can help or work with MySQL together. In his spare time he likes to spend time with his friends, travel around the world and play ultimate frisbee.
