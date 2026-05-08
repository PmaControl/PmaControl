---
title: Setting Up Percona PAM with Active Directory for External Authentication
source:
  name: Percona Blog
  url: https://www.percona.com/blog/setting-percona-pam-active-directory-external-authentication/
  post_id: 17089
source_author:
  name: Jaime Sicam
  slug: jaimesicam
  url: https://www.percona.com/blog/author/jaimesicam/
  website: ''
published_at: '2017-07-13T19:17:10'
published_at_gmt: '2017-07-13T19:17:10'
modified_at: '2026-05-05T18:44:46'
modified_at_gmt: '2026-05-05T18:44:46'
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
- Security
category_slugs:
- insight-for-dbas
- mysql
- percona-software
- security
tags:
- Percona PAM
- Percona Server for MySQL
- realmd
- SSSD
- Winbind
tag_slugs:
- percona-pam
- percona-server
- realmd
- sssd
- winbind
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Percona-PAM.jpg
image_count: 1
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Setting Up Percona PAM with Active Directory for External Authentication

Source: [Percona Blog](https://www.percona.com/blog/setting-percona-pam-active-directory-external-authentication/)

Auteur source: [Jaime Sicam](https://www.percona.com/blog/author/jaimesicam/)

Publication: 2017-07-13T19:17:10

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

In this blog post, we’ll look at how to set up Percona PAM with Active Directory for external authentication. In my previous article on Percona PAM, I demonstrated how to use Samba as a domain, and how easy it is to create domain users and groups via the samba-tool. Then we configured nss-pam-ldapd and nscd … Continued

## Structure detectee

- H2: Installing realmd and Its Dependencies
- H2: Joining the Domain via SSSD and Preparing It for Percona PAM
- H2: Joining the Domain via Winbind and Preparing it for Percona PAM
- H2: Conclusion

## Images et graphiques reperes

- featured / image: [Setting Up Percona PAM with Active Directory for External Authentication](https://www.percona.com/wp-content/uploads/2026/03/Percona-PAM.jpg)

## Auteur source

Jaime is a Senior Support Engineer at Percona. Prior to joining Percona, Jaime worked as a remote system administrator managing high-traffic websites and consultant for several local companies. He also conducted Linux trainings in several schools. Jaime is based in the Philippines. He enjoys road trips and photography in his spare time.
