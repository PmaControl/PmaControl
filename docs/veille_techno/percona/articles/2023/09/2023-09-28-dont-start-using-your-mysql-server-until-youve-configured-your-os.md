---
title: Don’t Start Using Your MySQL Server Until You’ve Configured Your OS
source:
  name: Percona Blog
  url: https://www.percona.com/blog/dont-start-using-your-mysql-server-until-youve-configured-your-os/
  post_id: 27525
source_author:
  name: Denis Subbota
  slug: denis-subbota
  url: https://www.percona.com/blog/author/denis-subbota/
  website: ''
published_at: '2023-09-28T14:46:18'
published_at_gmt: '2023-09-28T14:46:18'
modified_at: '2026-03-26T20:27:10'
modified_at_gmt: '2026-03-26T20:27:10'
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
- MySQL
- mysql-and-variants
tag_slugs:
- mysql
- mysql-and-variants
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Dont-Start-Using-Your-MySQL-Server-Until-Youve-Configured-Your-OS.png
image_count: 1
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Don’t Start Using Your MySQL Server Until You’ve Configured Your OS

Source: [Percona Blog](https://www.percona.com/blog/dont-start-using-your-mysql-server-until-youve-configured-your-os/)

Auteur source: [Denis Subbota](https://www.percona.com/blog/author/denis-subbota/)

Publication: 2023-09-28T14:46:18

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Whenever you install your favorite MySQL server on a freshly created Ubuntu instance, you start by updating the configuration for MySQL, such as configuring buffer pool, changing the default datadir director, and disabling one of the most outstanding features – query cache. It’s a nice thing to do, but first things first. Let’s review the … Continued

## Structure detectee

- H2: Memory
- H3: OOM
- H3: Swappiness
- H3: Transparent Huge Pages and Jemalloc
- H2: Action steps for memory settings
- H3: Disable THP
- H3: vm.swappiness = 1
- H3: OOM and Jemalloc
- H2: Mount point option for disk
- H2: Action steps to apply best practices for disk settings
- H3: Conclusion

## Images et graphiques reperes

- featured / image: [Don’t Start Using Your MySQL Server Until You’ve Configured Your OS](https://www.percona.com/wp-content/uploads/2026/03/Dont-Start-Using-Your-MySQL-Server-Until-Youve-Configured-Your-OS.png)

## Auteur source

Denis Subbota is a MySQL DBA I at Percona Managed Services since March 2021, previously honed his skills as an avionics technician at Uzbekistan Airways. With a passion for IT technology, he excels in database management and optimization, bringing valuable expertise to his current role.
