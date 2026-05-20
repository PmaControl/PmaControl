---
title: Beware of MySQL 5.6 Server UUID when Cloning Slaves
source:
  name: Percona Blog
  url: https://www.percona.com/blog/beware-mysql-5-6-server-uuid-cloning-slaves/
  post_id: 7728
source_author:
  name: Ovais Tariq
  slug: ovaistariq
  url: https://www.percona.com/blog/author/ovaistariq/
  website: http://www.percona.com/blog/
published_at: '2014-01-21T15:18:47'
published_at_gmt: '2014-01-21T15:18:47'
modified_at: '2026-05-04T22:14:31'
modified_at_gmt: '2026-05-04T22:14:31'
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
- Percona Services
category_slugs:
- insight-for-dbas
- mysql
- percona-services
tags:
- MySQL 5.6 new features
- MySQL 5.6 server UUID
- Ovais Tariq
- Replication
- Server UUID
tag_slugs:
- mysql-5-6-new-features
- mysql-5-6-server-uuid
- ovais-tariq
- replication
- server-uuid
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/MySQL-5.6-Server-UUID.png
image_count: 1
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Beware of MySQL 5.6 Server UUID when Cloning Slaves

Source: [Percona Blog](https://www.percona.com/blog/beware-mysql-5-6-server-uuid-cloning-slaves/)

Auteur source: [Ovais Tariq](https://www.percona.com/blog/author/ovaistariq/)

Publication: 2014-01-21T15:18:47

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

The other day I was working on an issue where one of the slaves was showing unexpected lag. Interestingly with only the IO thread running the slave was doing significantly more IO as compared to the rate at which the IO thread was fetching the binary log events from the master. I found this out … Continued

## Structure detectee

- H2: Importance of a Unique MySQL 5.6 Server UUID
- H2: Conclusion

## Images et graphiques reperes

- featured / image: [Beware of MySQL 5.6 Server UUID when Cloning Slaves](https://www.percona.com/wp-content/uploads/2026/03/MySQL-5.6-Server-UUID.png)
