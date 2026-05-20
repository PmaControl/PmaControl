---
title: Backing up Percona Server for MySQL with keyring_vault plugin enabled
source:
  name: Percona Blog
  url: https://www.percona.com/blog/backing-up-percona-server-for-mysql-with-keyring_vault-plugin-enabled/
  post_id: 19057
source_author:
  name: Jericho Rivera
  slug: jerichorivera
  url: https://www.percona.com/blog/author/jerichorivera/
  website: ''
published_at: '2018-09-24T12:34:09'
published_at_gmt: '2018-09-24T12:34:09'
modified_at: '2026-05-05T19:54:29'
modified_at_gmt: '2026-05-05T19:54:29'
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
- Encrypted Backups
- encryption
tag_slugs:
- encrypted-backups
- encryption
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Percona-XtraBackup-with-keyring_vault.jpg
image_count: 2
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Backing up Percona Server for MySQL with keyring_vault plugin enabled

Source: [Percona Blog](https://www.percona.com/blog/backing-up-percona-server-for-mysql-with-keyring_vault-plugin-enabled/)

Auteur source: [Jericho Rivera](https://www.percona.com/blog/author/jerichorivera/)

Publication: 2018-09-24T12:34:09

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

To use Percona XtraBackup with keyring_vault plugin enabled you need to take some special measures to secure a working backup. This post addresses how to backup Percona Server for MySQL with keyring_vault plugin enabled. We also run through the steps needed to restore the backup from the master to a slave. This is the second … Continued

## Structure detectee

- H4: Backing up from the master
- H4: Restoring the backup on the Slave server
- H4: Configure keyring_vault.conf on slave
- H4: Use –copy-back option to finalize backup restoration
- H4: Is validating your security strategy a concern?

## Images et graphiques reperes

- featured / image: [Backing up Percona Server for MySQL with keyring_vault plugin enabled](https://www.percona.com/wp-content/uploads/2026/03/Percona-XtraBackup-with-keyring_vault.jpg)
- content / image: [Percona XtraBackup with keyring_vault](https://www.percona.com/wp-content/uploads/2026/03/Percona-XtraBackup-with-keyring_vault-300x199.jpg)

## Auteur source

Jericho Rivera currently works for Percona as Support Engineer. His interests include linux systems and MySQL database administration.
