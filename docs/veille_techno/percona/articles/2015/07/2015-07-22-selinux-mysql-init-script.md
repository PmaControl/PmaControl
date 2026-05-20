---
title: SELinux and the MySQL init script
source:
  name: Percona Blog
  url: https://www.percona.com/blog/selinux-mysql-init-script/
  post_id: 9383
source_author:
  name: Stephane Combaudon
  slug: stc
  url: https://www.percona.com/blog/author/stc/
  website: ''
published_at: '2015-07-22T17:09:31'
published_at_gmt: '2015-07-22T17:09:31'
modified_at: '2026-05-04T22:38:57'
modified_at_gmt: '2026-05-04T22:38:57'
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
- Percona Software
category_slugs:
- mysql
- percona-software
tags:
- CentOS/RHEL 6
- init script
- MySQL
- Percona Server for MySQL
- Primary
- SELinux
- service mysql start
- Stephane Combaudon
tag_slugs:
- centos-rhel-6
- init-script
- mysql
- percona-server
- primary
- selinux
- service-mysql-start
- stephane-combaudon
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# SELinux and the MySQL init script

Source: [Percona Blog](https://www.percona.com/blog/selinux-mysql-init-script/)

Auteur source: [Stephane Combaudon](https://www.percona.com/blog/author/stc/)

Publication: 2015-07-22T17:09:31

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

I recently worked with a customer who had a weird issue: when their MySQL server was started (Percona Server 5.5), if they try to run service mysql start a second time, the init script was not able to detect that an instance was already running. As a result, it tried to start a second instance … Continued

## Structure detectee

- H2: Summary
- H2: How did we see the issue?
- H2: Investigation
- H2: Deeper investigation
- H2: The fix
- H2: Conclusion

## Auteur source

Stéphane joined Percona in July 2012, after working as a MySQL DBA for leading French companies such as Dailymotion and France Telecom. In real life, he lives in Paris with his wife and their twin daughters. When not in front of a computer or not spending time with his family, he likes playing chess and hiking.
