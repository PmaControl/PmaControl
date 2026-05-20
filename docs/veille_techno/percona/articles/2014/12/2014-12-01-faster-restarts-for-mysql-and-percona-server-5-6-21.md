---
title: Faster restarts for MySQL and Percona Server 5.6.21+
source:
  name: Percona Blog
  url: https://www.percona.com/blog/faster-restarts-for-mysql-and-percona-server-5-6-21/
  post_id: 8809
source_author:
  name: Stephane Combaudon
  slug: stc
  url: https://www.percona.com/blog/author/stc/
  website: ''
published_at: '2014-12-01T12:34:40'
published_at_gmt: '2014-12-01T12:34:40'
modified_at: '2026-05-04T22:30:38'
modified_at_gmt: '2026-05-04T22:30:38'
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
- Percona Software
category_slugs:
- insight-for-dbas
- mysql
- percona-software
tags:
- GTIDs
- MySQL
- Percona Server 5.6
- Primary
- simplified-binlog-gtid-recovery
- Stephane Combaudon
tag_slugs:
- gtids
- mysql
- percona-server-5-6
- primary
- simplified-binlog-gtid-recovery
- stephane-combaudon
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Dynamic-SQL-Workaround-in-MySQL.jpg
image_count: 2
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Faster restarts for MySQL and Percona Server 5.6.21+

Source: [Percona Blog](https://www.percona.com/blog/faster-restarts-for-mysql-and-percona-server-5-6-21/)

Auteur source: [Stephane Combaudon](https://www.percona.com/blog/author/stc/)

Publication: 2014-12-01T12:34:40

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

By default in MySQL 5.6, each time MySQL is started (regular start or crash recovery), it iterates through all the binlog files when GTIDs are not enabled. This can take a very long time if you have a large number of binary log files. MySQL and Percona Server 5.6.21+ have a fix with the simplified-binlog-gtid-recovery … Continued

## Structure detectee

- H2: Understanding the issue
- H2: The fix
- H2: Conclusion

## Images et graphiques reperes

- featured / image: [Faster restarts for MySQL and Percona Server 5.6.21+](https://www.percona.com/wp-content/uploads/2026/03/Dynamic-SQL-Workaround-in-MySQL.jpg)
- content / image: [Faster restarts for MySQL and Percona Server 5.6.21+](https://www.percona.com/wp-content/uploads/2026/03/percona_server-1-150x150.jpeg)

## Auteur source

Stéphane joined Percona in July 2012, after working as a MySQL DBA for leading French companies such as Dailymotion and France Telecom. In real life, he lives in Paris with his wife and their twin daughters. When not in front of a computer or not spending time with his family, he likes playing chess and hiking.
