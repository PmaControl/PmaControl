---
title: 'MySQL ring replication: Why it is a bad option'
source:
  name: Percona Blog
  url: https://www.percona.com/blog/mysql-ring-replication-why-it-is-a-bad-option/
  post_id: 8615
source_author:
  name: Stephane Combaudon
  slug: stc
  url: https://www.percona.com/blog/author/stc/
  website: ''
published_at: '2014-10-07T16:15:25'
published_at_gmt: '2014-10-07T16:15:25'
modified_at: '2026-03-25T17:43:04'
modified_at_gmt: '2026-03-25T17:43:04'
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
- High Availability
- MySQL ring replication
- Primary
- Stephane Combaudon
tag_slugs:
- high-availability
- mysql-ring-replication
- primary
- stephane-combaudon
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/ring1.png
image_count: 2
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# MySQL ring replication: Why it is a bad option

Source: [Percona Blog](https://www.percona.com/blog/mysql-ring-replication-why-it-is-a-bad-option/)

Auteur source: [Stephane Combaudon](https://www.percona.com/blog/author/stc/)

Publication: 2014-10-07T16:15:25

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

I’ve recently worked with customers using replication rings with 4+ servers; several servers accepting writes. The idea behind this design is always the same: by having multiple servers, you get high availability and by having multiple writer nodes, you get write scalability. Alas, this is simply not true. Here is why. High Availability Having several … Continued

## Structure detectee

- H2: High Availability
- H2: Write Scalability
- H2: Other concerns
- H2: Conclusion

## Images et graphiques reperes

- featured / image: [MySQL ring replication: Why it is a bad option](https://www.percona.com/wp-content/uploads/2026/03/ring1.png)
- content / image: [ring2](https://www.percona.com/wp-content/uploads/2026/03/ring2.png)

## Auteur source

Stéphane joined Percona in July 2012, after working as a MySQL DBA for leading French companies such as Dailymotion and France Telecom. In real life, he lives in Paris with his wife and their twin daughters. When not in front of a computer or not spending time with his family, he likes playing chess and hiking.
