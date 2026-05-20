---
title: Schema changes in MySQL for OpenStack Trove users
source:
  name: Percona Blog
  url: https://www.percona.com/blog/schema-changes-in-mysql-for-openstack-trove-users/
  post_id: 8630
source_author:
  name: Stephane Combaudon
  slug: stc
  url: https://www.percona.com/blog/author/stc/
  website: ''
published_at: '2014-11-24T08:00:56'
published_at_gmt: '2014-11-24T08:00:56'
modified_at: '2026-05-04T22:27:10'
modified_at_gmt: '2026-05-04T22:27:10'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
- Percona Toolkit
matched_filters:
- category:mysql:83
- search:percona-toolkit
categories:
- Insight for DBAs
- MySQL
category_slugs:
- insight-for-dbas
- mysql
tags:
- alter table
- MySQL
- OpenStack
- Primary
- pt-online-schema-change
- schema changes
- Stephane Combaudon
- Trove
tag_slugs:
- alter-table
- mysql
- openstack
- primary
- pt-online-schema-change
- schema-changes
- stephane-combaudon
- trove
featured_image_url: http://www.tesora.com/sites/default/files/diver-small.jpg
image_count: 1
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Schema changes in MySQL for OpenStack Trove users

Source: [Percona Blog](https://www.percona.com/blog/schema-changes-in-mysql-for-openstack-trove-users/)

Auteur source: [Stephane Combaudon](https://www.percona.com/blog/author/stc/)

Publication: 2014-11-24T08:00:56

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

People using OpenStack Trove instances can hit a common issue in the MySQL world: how to perform schema change operations while minimizing the impact on the database server? Let’s explore the options that can allow online schema changes. Summary With MySQL 5.5, pt-online-schema-change from Percona Toolkit is your best option for large tables while regular … Continued

## Structure detectee

- H2: Summary
- H2: Regular ALTER TABLE with MySQL 5.5
- H2: pt-online-schema-change
- H2: Metadata Locks
- H2: MySQL 5.6: Online Schema Changes?
- H2: Conclusion

## Images et graphiques reperes

- featured / image: [Schema changes in MySQL for OpenStack Trove users](http://www.tesora.com/sites/default/files/diver-small.jpg)

## Auteur source

Stéphane joined Percona in July 2012, after working as a MySQL DBA for leading French companies such as Dailymotion and France Telecom. In real life, he lives in Paris with his wife and their twin daughters. When not in front of a computer or not spending time with his family, he likes playing chess and hiking.
