---
title: Stored Functions and Temporary Tables are Not a Good Fit
source:
  name: Percona Blog
  url: https://www.percona.com/blog/stored-functions-and-temporary-tables-are-not-a-good-fit/
  post_id: 20493
source_author:
  name: Tibor Korocz
  slug: tibor-koroczpercona-com
  url: https://www.percona.com/blog/author/tibor-koroczpercona-com/
  website: ''
published_at: '2019-06-26T13:48:07'
published_at_gmt: '2019-06-26T13:48:07'
modified_at: '2026-05-04T21:02:40'
modified_at_gmt: '2026-05-04T21:02:40'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
matched_filters:
- category:mysql:83
categories:
- Insight for Developers
- MySQL
category_slugs:
- insight-for-developers
- mysql
tags:
- MySQL
- stored functions
tag_slugs:
- mysql
- stored-functions
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Stored-Functions.jpeg
image_count: 2
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Stored Functions and Temporary Tables are Not a Good Fit

Source: [Percona Blog](https://www.percona.com/blog/stored-functions-and-temporary-tables-are-not-a-good-fit/)

Auteur source: [Tibor Korocz](https://www.percona.com/blog/author/tibor-koroczpercona-com/)

Publication: 2019-06-26T13:48:07

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

In this blog post, I am going to show why we have to be careful with stored functions in select list, as a single query can cause thousands of queries in the background if we aren’t cautious. For this example, I am only going to use the SLEEP function to demonstrate the issue, but you … Continued

## Structure detectee

- H3: Using DML queries in these functions
- H3: How can we avoid this?
- H3: Where can I see if this is happening?
- H3: Conclusion

## Images et graphiques reperes

- featured / image: [Stored Functions and Temporary Tables are Not a Good Fit](https://www.percona.com/wp-content/uploads/2026/03/Stored-Functions.jpeg)
- content / image: [Stored Functions](https://www.percona.com/wp-content/uploads/2026/03/Stored-Functions-300x200.jpeg)

## Auteur source

Tibi joined Percona in 2015 as a Consultant. Before joining Percona, among many other things, he worked at the world’s largest car hire booking service as a Senior Database Engineer. He enjoys trying and working with the latest technologies and applications which can help or work with MySQL together. In his spare time he likes to spend time with his friends, travel around the world and play ultimate frisbee.
