---
title: 'Lock Down: Enforcing AppArmor with Percona XtraDB Cluster'
source:
  name: Percona Blog
  url: https://www.percona.com/blog/lock-down-enforcing-apparmor-with-percona-xtradb-cluster/
  post_id: 19122
source_author:
  name: Yves Trudeau
  slug: yves
  url: https://www.percona.com/blog/author/yves/
  website: http://www.percona.com/
published_at: '2018-08-09T11:26:15'
published_at_gmt: '2018-08-09T11:26:15'
modified_at: '2026-05-05T19:34:51'
modified_at_gmt: '2026-05-05T19:34:51'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
- XtraBackup
matched_filters:
- category:mysql:83
- search:percona-xtrabackup
- search:xtrabackup
categories:
- MySQL
- Percona Software
- Security
category_slugs:
- mysql
- percona-software
- security
tags: []
tag_slugs: []
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Enforcing-AppArmor-with-Percona-XtraDB-Cluster.jpg
image_count: 1
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Lock Down: Enforcing AppArmor with Percona XtraDB Cluster

Source: [Percona Blog](https://www.percona.com/blog/lock-down-enforcing-apparmor-with-percona-xtradb-cluster/)

Auteur source: [Yves Trudeau](https://www.percona.com/blog/author/yves/)

Publication: 2018-08-09T11:26:15

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Recently, I wrote a blog post showing how to enforce SELinux with Percona XtraDB Cluster (PXC). The Linux distributions derived from RedHat use SELinux. There is another major mandatory discretionary access control (DAC) system, AppArmor. Ubuntu, for example, installs AppArmor by default. If you are concerned by computer security and use PXC on Ubuntu, you … Continued

## Structure detectee

- H3: Install the tools
- H3: Create a skeleton profile
- H3: Get a well behaved SST script
- H3: Start iterating
- H4: Parse the logs with aa-logprof
- H4: Revise the profile
- H4: Copy the profile
- H3: Conclusion

## Images et graphiques reperes

- featured / image: [Lock Down: Enforcing AppArmor with Percona XtraDB Cluster](https://www.percona.com/wp-content/uploads/2026/03/Enforcing-AppArmor-with-Percona-XtraDB-Cluster.jpg)

## Auteur source

Yves is a Principal Architect at Percona, specializing in distributed technologies such as MySQL Cluster, Pacemaker and XtraDB cluster. He was previously a senior consultant for MySQL and Sun Microsystems. He holds a Ph.D. in Experimental Physics.
