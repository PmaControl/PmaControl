---
title: 'Set Theory in MySQL 8.0: UNION and Now INTERSECT and EXCEPT'
source:
  name: Percona Blog
  url: https://www.percona.com/blog/set-theory-in-mysql-8-0-union-and-now-intersect-and-except/
  post_id: 26155
source_author:
  name: Corrado Pandiani
  slug: corrado-pandiani
  url: https://www.percona.com/blog/author/corrado-pandiani/
  website: ''
published_at: '2022-11-02T13:16:07'
published_at_gmt: '2022-11-02T13:16:07'
modified_at: '2026-03-26T20:30:52'
modified_at_gmt: '2026-03-26T20:30:52'
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
- except
- intersect
- MySQL
- mysql-and-variants
- Percona Server for MySQL
- set theory
- union
tag_slugs:
- except
- intersect
- mysql
- mysql-and-variants
- percona-server
- set-theory
- union
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Set-Theory-in-MySQL-8.0.png
image_count: 7
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Set Theory in MySQL 8.0: UNION and Now INTERSECT and EXCEPT

Source: [Percona Blog](https://www.percona.com/blog/set-theory-in-mysql-8-0-union-and-now-intersect-and-except/)

Auteur source: [Corrado Pandiani](https://www.percona.com/blog/author/corrado-pandiani/)

Publication: 2022-11-02T13:16:07

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Are you familiar with the UNION statement for your SQL queries? Most likely, you are. It has been supported for a long time. In case you are not familiar with UNION, don’t worry, I’m going to show you how it works with simple examples. Considering “Set Theory”, other than the UNION, starting from the newly … Continued

## Structure detectee

- H2: The traditional UNION
- H2: INTERSECT
- H2: EXCEPT
- H2: Combine clauses to cover another case
- H4: Note:
- H2: Conclusion

## Images et graphiques reperes

- featured / image: [Set Theory in MySQL 8.0: UNION and Now INTERSECT and EXCEPT](https://www.percona.com/wp-content/uploads/2026/03/Set-Theory-in-MySQL-8.0.png)
- content / image: [Set Theory in MySQL 8.0](https://www.percona.com/wp-content/uploads/2026/03/Set-Theory-in-MySQL-8.0-300x157.png)
- content / image: [union.png](https://www.percona.com/wp-content/uploads/2026/03/union.png)
- content / image: [intersect.png](https://www.percona.com/wp-content/uploads/2026/03/intersect.png)
- content / image: [aexceptb.png](https://www.percona.com/wp-content/uploads/2026/03/aexceptb.png)
- content / image: [bexcepta.png](https://www.percona.com/wp-content/uploads/2026/03/bexcepta.png)
- content / image: [exceptunionexcept.png](https://www.percona.com/wp-content/uploads/2026/03/exceptunionexcept.png)

## Auteur source

Prior to joining Percona as a Senior Consultant, Corrado spent more than 20 years in developing web sites and designing and administering MySQL. He is a MySQL enthusiast since version 3.23 and his skills are focused on performances and architectural design. He's also a trainer and a MongoDB consultant.
