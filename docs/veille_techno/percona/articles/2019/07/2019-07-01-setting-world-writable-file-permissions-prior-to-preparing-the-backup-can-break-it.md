---
title: Setting World-Writable File Permissions Prior to Preparing the Backup Can Break It
source:
  name: Percona Blog
  url: https://www.percona.com/blog/setting-world-writable-file-permissions-prior-to-preparing-the-backup-can-break-it/
  post_id: 20530
source_author:
  name: Jaime Sicam
  slug: jaimesicam
  url: https://www.percona.com/blog/author/jaimesicam/
  website: ''
published_at: '2019-07-01T14:50:03'
published_at_gmt: '2019-07-01T14:50:03'
modified_at: '2026-04-27T21:18:19'
modified_at_gmt: '2026-04-27T21:18:19'
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
- tag:percona-xtrabackup:330
categories:
- Insight for DBAs
- MySQL
category_slugs:
- insight-for-dbas
- mysql
tags:
- Percona XtraBackup
tag_slugs:
- percona-xtrabackup
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Setting-World-Writable-File-Permissions.jpeg
image_count: 2
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Setting World-Writable File Permissions Prior to Preparing the Backup Can Break It

Source: [Percona Blog](https://www.percona.com/blog/setting-world-writable-file-permissions-prior-to-preparing-the-backup-can-break-it/)

Auteur source: [Jaime Sicam](https://www.percona.com/blog/author/jaimesicam/)

Publication: 2019-07-01T14:50:03

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

It’s bad practice to provide world-writable access to critical files in Linux, though we’ve seen time and time again that this is done to conveniently share files with other users, applications, or services. But with Xtrabackup, preparing backups could go wrong if the backup configuration has world-writable file permissions. Say you performed a backup on … Continued

## Images et graphiques reperes

- featured / image: [Setting World-Writable File Permissions Prior to Preparing the Backup Can Break It](https://www.percona.com/wp-content/uploads/2026/03/Setting-World-Writable-File-Permissions.jpeg)
- content / image: [Setting World-Writable File Permissions](https://www.percona.com/wp-content/uploads/2026/03/Setting-World-Writable-File-Permissions-300x200.jpeg)

## Auteur source

Jaime is a Senior Support Engineer at Percona. Prior to joining Percona, Jaime worked as a remote system administrator managing high-traffic websites and consultant for several local companies. He also conducted Linux trainings in several schools. Jaime is based in the Philippines. He enjoys road trips and photography in his spare time.
