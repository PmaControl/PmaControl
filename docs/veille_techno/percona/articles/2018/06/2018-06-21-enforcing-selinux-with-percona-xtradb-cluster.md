---
title: 'Lock Down: Enforcing SELinux with Percona XtraDB Cluster'
source:
  name: Percona Blog
  url: https://www.percona.com/blog/enforcing-selinux-with-percona-xtradb-cluster/
  post_id: 18919
source_author:
  name: Yves Trudeau
  slug: yves
  url: https://www.percona.com/blog/author/yves/
  website: http://www.percona.com/
published_at: '2018-06-21T15:57:27'
published_at_gmt: '2018-06-21T15:57:27'
modified_at: '2026-05-05T19:33:47'
modified_at_gmt: '2026-05-05T19:33:47'
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
tags:
- Linux
- SELinux
tag_slugs:
- linux
- selinux
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/selinux-pxc-security.jpg
image_count: 2
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Lock Down: Enforcing SELinux with Percona XtraDB Cluster

Source: [Percona Blog](https://www.percona.com/blog/enforcing-selinux-with-percona-xtradb-cluster/)

Auteur source: [Yves Trudeau](https://www.percona.com/blog/author/yves/)

Publication: 2018-06-21T15:57:27

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Why do I spend time blogging about security frameworks? Because, although there are some resources available on the Web, none apply to Percona XtraDB Cluster (PXC) directly. Actually, I rarely encounter a MySQL setup where SELinux is enforced and never when Percona XtraDB Cluster (PXC) or another Galera replication implementation is used. As we’ll see, … Continued

## Structure detectee

- H4: Some context
- H4: Starting point
- H4: First run
- H4: Troubleshooting
- H4: TCP ports
- H4: Non-default paths
- H4: Variables check list
- H4: All together
- H4: Conclusion

## Images et graphiques reperes

- featured / image: [Lock Down: Enforcing SELinux with Percona XtraDB Cluster](https://www.percona.com/wp-content/uploads/2026/03/selinux-pxc-security.jpg)
- content / image: [SELinux for PXC security](https://www.percona.com/wp-content/uploads/2026/03/selinux-pxc-security-300x200.jpg)

## Auteur source

Yves is a Principal Architect at Percona, specializing in distributed technologies such as MySQL Cluster, Pacemaker and XtraDB cluster. He was previously a senior consultant for MySQL and Sun Microsystems. He holds a Ph.D. in Experimental Physics.
