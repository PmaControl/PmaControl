---
title: How to Have a Single MySQL User Account From Specific Hosts
source:
  name: Percona Blog
  url: https://www.percona.com/blog/how-to-have-a-single-mysql-user-account-from-specific-hosts/
  post_id: 28811
source_author:
  name: Totel
  slug: aristotle-po
  url: https://www.percona.com/blog/author/aristotle-po/
  website: ''
published_at: '2024-07-18T12:47:36'
published_at_gmt: '2024-07-18T12:47:36'
modified_at: '2026-03-26T20:26:07'
modified_at_gmt: '2026-03-26T20:26:07'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
- ProxySQL
matched_filters:
- category:mysql:83
- search:proxysql
categories:
- Insight for DBAs
- MySQL
category_slugs:
- insight-for-dbas
- mysql
tags:
- MySQL
- mysql-and-variants
tag_slugs:
- mysql
- mysql-and-variants
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/How-to-Have-a-Single-MySQL-User-Account-From-Specific-Hosts.jpg
image_count: 1
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# How to Have a Single MySQL User Account From Specific Hosts

Source: [Percona Blog](https://www.percona.com/blog/how-to-have-a-single-mysql-user-account-from-specific-hosts/)

Auteur source: [Totel](https://www.percona.com/blog/author/aristotle-po/)

Publication: 2024-07-18T12:47:36

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

In this article, we will demonstrate how to have a single MySQL database user account that can connect from specific hosts. We would usually implement it by creating separate user accounts with the same username but different hosts/IPs like <USER>@<HOST1>, <USER>@<HOST2> …. <USER>@<HOSTn>. Then, give those users the same grants(privileges/roles) and settings(password, SSL, etc). Instead … Continued

## Structure detectee

- H3: Process
- H3: Considerations
- H3: Conclusion

## Images et graphiques reperes

- featured / image: [How to Have a Single MySQL User Account From Specific Hosts](https://www.percona.com/wp-content/uploads/2026/03/How-to-Have-a-Single-MySQL-User-Account-From-Specific-Hosts.jpg)

## Auteur source

Supports MySQL with previous experience in Oracle and PostgreSQL databases. Likes Bash and SQL scripting. Uses Ubuntu for workstation and RHEL derivatives for VM/Container. Hobbies are gardening and pets(dogs, chickens and ducks).
