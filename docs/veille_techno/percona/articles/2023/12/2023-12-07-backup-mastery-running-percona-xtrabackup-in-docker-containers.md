---
title: 'Backup Mastery: Running Percona XtraBackup in Docker Containers'
source:
  name: Percona Blog
  url: https://www.percona.com/blog/backup-mastery-running-percona-xtrabackup-in-docker-containers/
  post_id: 27783
source_author:
  name: Mohit Joshi
  slug: mohit-joshi
  url: https://www.percona.com/blog/author/mohit-joshi/
  website: ''
published_at: '2023-12-07T17:32:49'
published_at_gmt: '2023-12-07T17:32:49'
modified_at: '2026-03-26T20:26:54'
modified_at_gmt: '2026-03-26T20:26:54'
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
- Percona Software
category_slugs:
- insight-for-dbas
- mysql
- percona-software
tags:
- MySQL
- mysql-and-variants
- Percona Software
- Percona XtraBackup
tag_slugs:
- mysql
- mysql-and-variants
- percona-software
- percona-xtrabackup
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Running-Percona-XtraBackup-in-Docker-Containers.png
image_count: 1
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Backup Mastery: Running Percona XtraBackup in Docker Containers

Source: [Percona Blog](https://www.percona.com/blog/backup-mastery-running-percona-xtrabackup-in-docker-containers/)

Auteur source: [Mohit Joshi](https://www.percona.com/blog/author/mohit-joshi/)

Publication: 2023-12-07T17:32:49

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Ensuring the security and resilience of your data hinges on having a robust backup strategy, and Percona XtraBackup (PXB), our open source backup solution for all versions of MySQL, is designed to make backups a seamless procedure without disrupting the performance of your server in a production environment. When combined with the versatility of Docker … Continued

## Structure detectee

- H3: Working with Percona Server for MySQL 8.1 and PXB 8.1 Docker images
- H4: Start a Percona Server for MySQL 8.1 instance in a Docker container
- H4: Add data to the database
- H4: Run Percona XtraBackup 8.1 in a container, take a backup, and prepare
- H4: Stop the Percona Server container
- H4: Remove the MySQL data directory
- H4: Run Percona XtraBackup 8.1 in a container to restore the backup
- H4: Start the Percona Server container to verify the restored data
- H4: Summary

## Images et graphiques reperes

- featured / image: [Backup Mastery: Running Percona XtraBackup in Docker Containers](https://www.percona.com/wp-content/uploads/2026/03/Running-Percona-XtraBackup-in-Docker-Containers.png)

## Auteur source

Mohit Joshi is a Senior QA Engineer hailing from India. He is working in Percona for the last 5 years and has more than 11 years of experience in the MySQL ecospace.
