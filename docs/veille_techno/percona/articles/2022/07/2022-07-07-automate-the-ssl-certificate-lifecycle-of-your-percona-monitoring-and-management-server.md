---
title: Automate the SSL Certificate Lifecycle of your Percona Monitoring and Management Server
source:
  name: Percona Blog
  url: https://www.percona.com/blog/automate-the-ssl-certificate-lifecycle-of-your-percona-monitoring-and-management-server/
  post_id: 25747
source_author:
  name: Alexander Demidoff
  slug: alexander-demidoff
  url: https://www.percona.com/blog/author/alexander-demidoff/
  website: ''
published_at: '2022-07-07T12:08:36'
published_at_gmt: '2022-07-07T12:08:36'
modified_at: '2026-05-05T16:43:57'
modified_at_gmt: '2026-05-05T16:43:57'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- PMM
matched_filters:
- category:monitoring:2104
- search:percona-monitoring-and-management
- search:pmm
- tag:pmm:2167
categories:
- Monitoring
- Percona Software
- Security
category_slugs:
- monitoring
- percona-software
- security
tags:
- Monitoring
- PMM
- security
tag_slugs:
- monitoring
- pmm
- security
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/SSL-Certificate-Lifecycle-of-your-Percona-Monitoring-and-Management.png
image_count: 7
graph_or_chart_count: 1
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Automate the SSL Certificate Lifecycle of your Percona Monitoring and Management Server

Source: [Percona Blog](https://www.percona.com/blog/automate-the-ssl-certificate-lifecycle-of-your-percona-monitoring-and-management-server/)

Auteur source: [Alexander Demidoff](https://www.percona.com/blog/author/alexander-demidoff/)

Publication: 2022-07-07T12:08:36

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

We highly value security here at Percona, and in this blog post, we will show how to protect your Percona Monitoring and Management (PMM) Server with an SSL certificate and automate its lifecycle by leveraging a proxy server. Introduction As you may know, PMM Server provides a self-signed SSL certificate out-of-the-box to encrypt traffic between … Continued

## Structure detectee

- H2: Introduction
- H2: Reverse proxies
- H2: Nginx
- H3: Restrict access to PMM
- H3: Test the connection security
- H2: Traefik
- H3: Basic configuration
- H3: Advanced configuration
- H2: Some security aspects
- H3: Docker socket
- H3: The use of port 80
- H2: Conclusion

## Images et graphiques reperes

- featured / image: [Automate the SSL Certificate Lifecycle of your Percona Monitoring and Management Server](https://www.percona.com/wp-content/uploads/2026/03/SSL-Certificate-Lifecycle-of-your-Percona-Monitoring-and-Management.png)
- content / image: [SSL Certificate Lifecycle of your Percona Monitoring and Management](https://www.percona.com/wp-content/uploads/2026/03/SSL-Certificate-Lifecycle-of-your-Percona-Monitoring-and-Management-300x157.png)
- content / graph_or_chart: [PMM Server and Client Network Diagram](https://www.percona.com/wp-content/uploads/2026/03/Reverse-proxy-Reverse-proxy-diagram-scaled.jpg)
- content / image: [Proxy-configuration-A-plus-report-1024x530.png](https://www.percona.com/wp-content/uploads/2026/03/Proxy-configuration-A-plus-report-1024x530.png)
- content / image: [Proxy configuration - B-flat report](https://www.percona.com/wp-content/uploads/2026/03/Proxy-configuration-B-flat-report-1024x572.png)
- content / image: [Proxy configuration - weak ciphers](https://www.percona.com/wp-content/uploads/2026/03/Proxy-configuration-weak-ciphers-1024x552.png)
- content / image: [Proxy configuration - A-plus superior report](https://www.percona.com/wp-content/uploads/2026/03/Proxy-configuration-A-plus-superior-report-1024x527.png)

## Auteur source

I'm a software engineer and architect with decades of experience in the software industry. In the past, I developed scalable web apps powered by open source databases. Now I'm contributing to the software experiences of the users of our own monitoring and management solution known as PMM.
