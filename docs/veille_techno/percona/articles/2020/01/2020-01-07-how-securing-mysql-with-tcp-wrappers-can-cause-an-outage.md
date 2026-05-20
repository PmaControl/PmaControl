---
title: How Securing MySQL with TCP Wrappers Can Cause an Outage
source:
  name: Percona Blog
  url: https://www.percona.com/blog/how-securing-mysql-with-tcp-wrappers-can-cause-an-outage/
  post_id: 21363
source_author:
  name: Ananias Tsalouchidis
  slug: ananias-tsalouchidis
  url: https://www.percona.com/blog/author/ananias-tsalouchidis/
  website: ''
published_at: '2020-01-07T17:05:08'
published_at_gmt: '2020-01-07T17:05:08'
modified_at: '2026-05-05T16:23:03'
modified_at_gmt: '2026-05-05T16:23:03'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
matched_filters:
- category:mysql:83
categories:
- MySQL
- Security
category_slugs:
- mysql
- security
tags:
- dns
- MySQL
- outage
- skip_name_resolve
- stall
- tcp wrappers
tag_slugs:
- dns
- mysql
- outage
- skip_name_resolve
- stall
- tcp-wrappers
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/securing-mysql-tcp.png
image_count: 2
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# How Securing MySQL with TCP Wrappers Can Cause an Outage

Source: [Percona Blog](https://www.percona.com/blog/how-securing-mysql-with-tcp-wrappers-can-cause-an-outage/)

Auteur source: [Ananias Tsalouchidis](https://www.percona.com/blog/author/ananias-tsalouchidis/)

Publication: 2020-01-07T17:05:08

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

The Case Securing MySQL is always a challenge. There are general best practices that can be followed for securing your installation, but the more complex setup you have the more likely you are to face some issues which can be difficult to troubleshoot. We’ve recently been working on a case (thanks Alok Pathak and Janos … Continued

## Structure detectee

- H3: The Case
- H3: How We Approached This Issue to Find the Root Cause
- H3: The Root Cause
- H3: Conclusion

## Images et graphiques reperes

- featured / image: [How Securing MySQL with TCP Wrappers Can Cause an Outage](https://www.percona.com/wp-content/uploads/2026/03/securing-mysql-tcp.png)
- content / image: [Screenshot-2019-12-17-at-12.46.13-1024x346.png](https://www.percona.com/wp-content/uploads/2026/03/Screenshot-2019-12-17-at-12.46.13-1024x346.png)

## Auteur source

Ananias is a Principal MySQL DBA who joined Percona on May 2017. He holds a BSc and a MSc in computer science and has a 10+ years working experience as a systems and databases administrator. He loves databases and perl scripting. He has worked for big companies and academic institutions and has also been involved into numerous research programs.
