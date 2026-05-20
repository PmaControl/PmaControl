---
title: How To Use systemd in Linux to Configure and Manage Multiple MySQL Instances
source:
  name: Percona Blog
  url: https://www.percona.com/blog/how-to-use-systemd-in-linux-to-configure-and-manage-multiple-mysql-instances/
  post_id: 27382
source_author:
  name: Mughees Ahmed
  slug: mughees-ahmed
  url: https://www.percona.com/blog/author/mughees-ahmed/
  website: ''
published_at: '2023-08-18T13:58:32'
published_at_gmt: '2023-08-18T13:58:32'
modified_at: '2026-03-26T20:29:11'
modified_at_gmt: '2026-03-26T20:29:11'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
- PMM
matched_filters:
- category:mysql:83
- search:percona-monitoring-and-management
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
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Use-systemd-in-Linux-to-Configure-and-Manage-Multiple-MySQL-Instances.jpeg
image_count: 1
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# How To Use systemd in Linux to Configure and Manage Multiple MySQL Instances

Source: [Percona Blog](https://www.percona.com/blog/how-to-use-systemd-in-linux-to-configure-and-manage-multiple-mysql-instances/)

Auteur source: [Mughees Ahmed](https://www.percona.com/blog/author/mughees-ahmed/)

Publication: 2023-08-18T13:58:32

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

This blog describes how to configure systemd for multiple instances of MySQL. With package installations of MySQL using YUM or APT, it’s easy to manage MySQL with systemctl, but how will you manage it when you install from the generic binaries? Here, we will configure multiple MySQL instances from the generic binaries and manage them … Continued

## Structure detectee

- H3: Why do you need multiple instances on the same server?
- H3: Install MySQL
- H3: Create MySQL configuration for each instance
- H3: Initialize instance
- H3: Configured the systemd service
- H3: Conclusion

## Images et graphiques reperes

- featured / image: [How To Use systemd in Linux to Configure and Manage Multiple MySQL Instances](https://www.percona.com/wp-content/uploads/2026/03/Use-systemd-in-Linux-to-Configure-and-Manage-Multiple-MySQL-Instances.jpeg)

## Auteur source

Over 5 years of experience in Administration in MySQL databases using various tools and technologies. Keen on learning new database technologies and having very good analytical skills. Working knowledge of Red Hat Linux, UNIX, Solaris, AWS, and GCP.
