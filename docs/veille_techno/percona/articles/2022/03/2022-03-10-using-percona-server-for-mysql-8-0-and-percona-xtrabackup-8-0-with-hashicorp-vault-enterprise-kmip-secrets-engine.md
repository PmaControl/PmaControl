---
title: Using Percona Server for MySQL 8.0 and Percona XtraBackup 8.0 with HashiCorp Vault Enterprise KMIP Secrets Engine
source:
  name: Percona Blog
  url: https://www.percona.com/blog/using-percona-server-for-mysql-8-0-and-percona-xtrabackup-8-0-with-hashicorp-vault-enterprise-kmip-secrets-engine/
  post_id: 25472
source_author:
  name: Manish Chawla
  slug: manish-chawla
  url: https://www.percona.com/blog/author/manish-chawla/
  website: ''
published_at: '2022-03-10T14:56:47'
published_at_gmt: '2022-03-10T14:56:47'
modified_at: '2026-04-28T15:05:10'
modified_at_gmt: '2026-04-28T15:05:10'
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
- Insight for Developers
- MySQL
- Percona Software
category_slugs:
- insight-for-dbas
- insight-for-developers
- mysql
- percona-software
tags:
- MySQL
- mysql-and-variants
- Percona Server for MySQL
- Percona Software
- Percona XtraBackup
tag_slugs:
- mysql
- mysql-and-variants
- percona-server
- percona-software
- percona-xtrabackup
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/HashiCorp-Vault-Enterprise-KMIP-Secrets-Engine.png
image_count: 2
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Using Percona Server for MySQL 8.0 and Percona XtraBackup 8.0 with HashiCorp Vault Enterprise KMIP Secrets Engine

Source: [Percona Blog](https://www.percona.com/blog/using-percona-server-for-mysql-8-0-and-percona-xtrabackup-8-0-with-hashicorp-vault-enterprise-kmip-secrets-engine/)

Auteur source: [Manish Chawla](https://www.percona.com/blog/author/manish-chawla/)

Publication: 2022-03-10T14:56:47

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

KMIP (Key Management Interoperability Protocol) is an open standard developed by OASIS (Organization for Advancement of Structured Information Standards) for the encryption of stored data and cryptographic key management. Percona Server for MySQL 8.0.27 and Percona XtraBackup 8.0.27 now include a KMIP keyring plugin to enable the exchange of cryptographic keys between a key management … Continued

## Structure detectee

- H2: Install Hashicorp Vault Enterprise
- H2: Configure KMIP Secrets Engine in Vault
- H2: Percona Server for MySQL 8.0.27 Configuration for KMIP
- H2: Backup and Restore of Percona Server for MySQL 8.0.27 Using Percona XtraBackup 8.0.27

## Images et graphiques reperes

- featured / image: [Using Percona Server for MySQL 8.0 and Percona XtraBackup 8.0 with HashiCorp Vault Enterprise KMIP Secrets Engine](https://www.percona.com/wp-content/uploads/2026/03/HashiCorp-Vault-Enterprise-KMIP-Secrets-Engine.png)
- content / image: [Percona HashiCorp Vault Enterprise KMIP Secrets Engine](https://www.percona.com/wp-content/uploads/2026/03/HashiCorp-Vault-Enterprise-KMIP-Secrets-Engine-300x157.png)

## Auteur source

Manish joined Percona in 2018 and works in the QA team. Having worked in different roles, domains, and companies, he now tests Percona Server for MySQL, Percona XtraBackup, and Percona XtraDB Cluster. He likes testing complex distributed systems and has good experience in test planning and strategy.
