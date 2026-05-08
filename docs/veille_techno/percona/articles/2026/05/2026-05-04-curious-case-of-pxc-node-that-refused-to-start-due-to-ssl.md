---
title: Curious case of PXC node that refused to start due to SSL
source:
  name: Percona Blog
  url: https://www.percona.com/blog/curious-case-of-pxc-node-that-refused-to-start-due-to-ssl/
  post_id: 43312
source_author:
  name: Kedar Vaijanapurkar
  slug: kedar-vaijanapurkar
  url: https://www.percona.com/blog/author/kedar-vaijanapurkar/
  website: http://kedar.nitty-witty.com/blog
published_at: '2026-05-04T05:45:15'
published_at_gmt: '2026-05-04T05:45:15'
modified_at: '2026-05-04T05:45:15'
modified_at_gmt: '2026-05-04T05:45:15'
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
- MySQL
category_slugs:
- insight-for-dbas
- mysql
tags:
- database troubleshooting case study
- FUTURE crypto policy
- Galera cluster failure
- Linux security policy
- MySQL Galera SSL
- MySQL SSL error
- OpenSSL error debugging
- OpenSSL policy impact
- Percona XtraDB Cluster
- PXC troubleshooting
- RSA key too small
- SSL_CTX_use_certificate error
- update-crypto-policies
tag_slugs:
- database-troubleshooting-case-study
- future-crypto-policy
- galera-cluster-failure
- linux-security-policy
- mysql-galera-ssl
- mysql-ssl-error
- openssl-error-debugging
- openssl-policy-impact
- percona-xtradb-cluster
- pxc-troubleshooting
- rsa-key-too-small
- ssl_ctx_use_certificate-error
- update-crypto-policies
featured_image_url: https://www.percona.com/wp-content/uploads/2026/04/goat-detective.png
image_count: 1
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Curious case of PXC node that refused to start due to SSL

Source: [Percona Blog](https://www.percona.com/blog/curious-case-of-pxc-node-that-refused-to-start-due-to-ssl/)

Auteur source: [Kedar Vaijanapurkar](https://www.percona.com/blog/author/kedar-vaijanapurkar/)

Publication: 2026-05-04T05:45:15

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

In this blog, I am going to share a real-world debugging case study where a routine Percona XtraDB Cluster node restart led to an unexpected failure. I will walk through what we observed, what we checked, and how we ultimately identified the root cause. Let’s see how the maintenance goes. It was supposed to be … Continued

## Structure detectee

- H2: The Problem
- H2: Checking Usual Suspects
- H2: The Clue
- H2: Fixture
- H2: Conclusion

## Images et graphiques reperes

- featured / image: [Curious case of PXC node that refused to start due to SSL](https://www.percona.com/wp-content/uploads/2026/04/goat-detective.png)

## Auteur source

Kedar Vaijanapurkar is a Tier 2 MySQL DBA at Percona since Oct 2021. He's experienced in MySQL related technologies and constantly working on improving skills. He lives in the cultural city of Vadodara with his SPOF (wife) and two HA (highly active) replicas.
