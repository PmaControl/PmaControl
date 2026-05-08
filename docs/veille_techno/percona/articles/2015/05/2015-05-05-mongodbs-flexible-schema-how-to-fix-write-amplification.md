---
title: 'MongoDB’s flexible schema: How to fix write amplification'
source:
  name: Percona Blog
  url: https://www.percona.com/blog/mongodbs-flexible-schema-how-to-fix-write-amplification/
  post_id: 9246
source_author:
  name: Stephane Combaudon
  slug: stc
  url: https://www.percona.com/blog/author/stc/
  website: ''
published_at: '2015-05-05T15:56:11'
published_at_gmt: '2015-05-05T15:56:11'
modified_at: '2026-05-04T22:37:11'
modified_at_gmt: '2026-05-04T22:37:11'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
matched_filters:
- category:mysql:83
categories:
- MySQL
category_slugs:
- mysql
tags:
- compression
- flexible schema
- MongoDB
- MySQL
- Percona
- Primary
- Stephane Combaudon
- tokumx
- write amplification
tag_slugs:
- compression
- flexible-schema
- mongodb
- mysql
- cap-percona
- primary
- stephane-combaudon
- tokumx
- write-amplification
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# MongoDB’s flexible schema: How to fix write amplification

Source: [Percona Blog](https://www.percona.com/blog/mongodbs-flexible-schema-how-to-fix-write-amplification/)

Auteur source: [Stephane Combaudon](https://www.percona.com/blog/author/stc/)

Publication: 2015-05-05T15:56:11

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Being schemaless is one of the key features of MongoDB. On the bright side this allows developers to easily modify the schema of their collections without waiting for the database to be ready to accept a new schema. However schemaless is not free and one of the drawbacks is write amplification. Let’s focus on that … Continued

## Structure detectee

- H2: Write amplification?
- H2: Compression
- H2: Conclusion

## Auteur source

Stéphane joined Percona in July 2012, after working as a MySQL DBA for leading French companies such as Dailymotion and France Telecom. In real life, he lives in Paris with his wife and their twin daughters. When not in front of a computer or not spending time with his family, he likes playing chess and hiking.
