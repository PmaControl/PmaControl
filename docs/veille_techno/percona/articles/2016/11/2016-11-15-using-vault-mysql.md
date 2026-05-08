---
title: Using Vault with MySQL
source:
  name: Percona Blog
  url: https://www.percona.com/blog/using-vault-mysql/
  post_id: 15845
source_author:
  name: Ceri Williams
  slug: ceri-williams
  url: https://www.percona.com/blog/author/ceri-williams/
  website: https://www.percona.com
published_at: '2016-11-15T00:31:34'
published_at_gmt: '2016-11-15T00:31:34'
modified_at: '2026-05-05T18:22:23'
modified_at_gmt: '2026-05-05T18:22:23'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
matched_filters:
- category:mysql:83
categories:
- Insight for DBAs
- Insight for Developers
- MySQL
- Security
category_slugs:
- insight-for-dbas
- insight-for-developers
- mysql
- security
tags:
- encrypt
- grants
- MySQL
- roles
- SSL
- using vault
- Vault
tag_slugs:
- encrypt
- grants
- mysql
- roles
- ssl
- using-vault
- vault
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/MySQL-Data-at-Rest-Encryption-1-e1476292822388.jpg
image_count: 2
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Using Vault with MySQL

Source: [Percona Blog](https://www.percona.com/blog/using-vault-mysql/)

Auteur source: [Ceri Williams](https://www.percona.com/blog/author/ceri-williams/)

Publication: 2016-11-15T00:31:34

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Using Vault with MySQL In my previous post I discussed using GPG to secure your database credentials. This relies on a local copy of your MySQL client config, but what if you want to keep the credentials stored safely along with other super secret information? Sure, GPG could still be used, but there must be … Continued

## Structure detectee

- H2: Download time
- H2: Let’s Encrypt… why not?
- H3: Automating renewal
- H2: Getting started with Vault
- H3: Starting Vault
- H2: Setting up a policy
- H2: TLS authentication for MySQL secrets
- H3: Generate a client certificate using OpenSSL
- H3: Adding an authorisation certificate to Vault
- H2: Ssshh!! It’s secret!
- H2: On-demand MySQL grants

## Images et graphiques reperes

- featured / image: [Using Vault with MySQL](https://www.percona.com/wp-content/uploads/2026/03/MySQL-Data-at-Rest-Encryption-1-e1476292822388.jpg)
- content / image: [Encrypt your secrets and use Vault with MySQL](https://www.percona.com/wp-content/uploads/2026/03/MySQL-Data-at-Rest-Encryption.jpg)
  Caption: Using Vault with MySQL

## Auteur source

Ceri is a Senior Technical Operations Engineer at Percona. He has previously worked in a variety of industries ranging from telecoms to skin care and online travel, nearly always with a database by his side for more than 10 years. Living in the Welsh Marches area of the UK, Ceri enjoys the rural life and beautiful countryside whenever possible.
