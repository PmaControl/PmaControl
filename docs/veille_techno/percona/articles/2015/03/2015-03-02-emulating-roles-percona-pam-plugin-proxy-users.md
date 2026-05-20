---
title: Emulating MySQL roles with the Percona PAM plugin and proxy users
source:
  name: Percona Blog
  url: https://www.percona.com/blog/emulating-roles-percona-pam-plugin-proxy-users/
  post_id: 9086
source_author:
  name: Stephane Combaudon
  slug: stc
  url: https://www.percona.com/blog/author/stc/
  website: ''
published_at: '2015-03-02T16:50:12'
published_at_gmt: '2015-03-02T16:50:12'
modified_at: '2026-05-04T22:35:12'
modified_at_gmt: '2026-05-04T22:35:12'
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
- Percona Software
category_slugs:
- insight-for-dbas
- mysql
- percona-software
tags:
- HIPAA
- MySQL roles
- PCI
- Percona PAM plugin
- Percona Server for MySQL
- Primary
- proxy users
- Stephane Combaudon
tag_slugs:
- hipaa
- mysql-roles
- pci
- percona-pam-plugin
- percona-server
- primary
- proxy-users
- stephane-combaudon
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Emulating MySQL roles with the Percona PAM plugin and proxy users

Source: [Percona Blog](https://www.percona.com/blog/emulating-roles-percona-pam-plugin-proxy-users/)

Auteur source: [Stephane Combaudon](https://www.percona.com/blog/author/stc/)

Publication: 2015-03-02T16:50:12

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

From time to time people wonder how to implement roles in MySQL. This can be useful for companies having to deal with many user accounts or for companies with tight security requirements (PCI or HIPAA for instance). Roles do not exist in regular MySQL but here is an example on how to emulate them using … Continued

## Structure detectee

- H2: The goal
- H2: Setting up the Percona PAM plugin
- H2: Testing authentication with the PAM plugin
- H2: Creating proxy user
- H2: Creating the proxied accounts
- H2: Creating the Unix user accounts
- H2: Testing it out!
- H2: Alternatives
- H2: Conclusion

## Auteur source

Stéphane joined Percona in July 2012, after working as a MySQL DBA for leading French companies such as Dailymotion and France Telecom. In real life, he lives in Paris with his wife and their twin daughters. When not in front of a computer or not spending time with his family, he likes playing chess and hiking.
