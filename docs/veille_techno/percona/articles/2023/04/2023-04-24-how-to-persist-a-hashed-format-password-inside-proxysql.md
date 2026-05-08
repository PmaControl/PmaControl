---
title: How to Persist a Hashed Format Password Inside ProxySQL
source:
  name: Percona Blog
  url: https://www.percona.com/blog/how-to-persist-a-hashed-format-password-inside-proxysql/
  post_id: 26924
source_author:
  name: Abhinav Gupta
  slug: abhinav-gupta
  url: https://www.percona.com/blog/author/abhinav-gupta/
  website: ''
published_at: '2023-04-24T13:13:46'
published_at_gmt: '2023-04-24T13:13:46'
modified_at: '2026-03-26T20:29:44'
modified_at_gmt: '2026-03-26T20:29:44'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
- ProxySQL
matched_filters:
- category:mysql:83
- category:proxysql:2261
- search:proxysql
categories:
- MySQL
- ProxySQL
- Security
category_slugs:
- mysql
- proxysql
- security
tags:
- MySQL
- mysql-and-variants
- ProxySQL
- security
tag_slugs:
- mysql
- mysql-and-variants
- proxysql
- security
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/lucas.speyer_database_monitoring_blue_navy_colored_texture_35588dbb-cc91-4a19-bb33-743dccb4b525-1.png
image_count: 1
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# How to Persist a Hashed Format Password Inside ProxySQL

Source: [Percona Blog](https://www.percona.com/blog/how-to-persist-a-hashed-format-password-inside-proxysql/)

Auteur source: [Abhinav Gupta](https://www.percona.com/blog/author/abhinav-gupta/)

Publication: 2023-04-24T13:13:46

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

In this blog post, we will see how to persist the password inside the ProxySQL mysql_users table in hashed format only. Also, even if someone stored the password in cleartext, we see how to change those into the hashed format easily. Here we are just highlighting one of the scenarios during work on the client … Continued

## Structure detectee

- H2: Password formats inside ProxySQL
- H2: ProxySQL’s admin-hash_passwords variable
- H2: Let’s persist the hashed password inside ProxySQL
- H2: Conclusion

## Images et graphiques reperes

- featured / image: [How to Persist a Hashed Format Password Inside ProxySQL](https://www.percona.com/wp-content/uploads/2026/03/lucas.speyer_database_monitoring_blue_navy_colored_texture_35588dbb-cc91-4a19-bb33-743dccb4b525-1.png)
