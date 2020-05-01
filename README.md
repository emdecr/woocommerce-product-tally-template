<h1 align="center">
    Woocommerce Product Tally Template
</h1>

<p align="center"><em>Because sometimes you just need a quick tally</em></p>

<p align="center">
    <a href="https://github.com/emdecr/life-overview-base/releases">
        <img src="https://img.shields.io/badge/release-v1.0-blue.svg" alt="release badge version 1.0">
    </a>
    <a href="https://emilydelacruz.com">
        <img src="https://img.shields.io/badge/%3C%2F%3E%20with%20%E2%99%A5%EF%B8%8E%20by-emdecr-red.svg" alt="emdecr badge">
    </a>
    
</p>

# Intro

Author: Emily Dela Cruz

## Context + Problem

### Context

<div style="max-width: 600px">
    In the wake of the COVID-19 outbreak, many restaurants added online ordering to their websites.
    
    One of my clients had a consistently changing roster of chefs that cooked up different meals folks could order and pick up on a certain date. WooCommerce (WC) powered the online store. Each meal was a Woocommerce Product tagged with a specific Category (ie. Chef/Event).
    
    Some Chefs/Events were recurring, and orders were only differentiated by the date. A Chef/Event could have a chunk of orders for one date range, and then another chunk for a date range weeks/months later.
</div>

### Problem

<div style="max-width: 600px">
    There's no easy way to get a count for Products related to a specific WC Category. You can get a list of Orders linked to a specific WC Category, but then you'd have to go into each individual Order and manually count Products.
    
    My client needed a simple count of how many of each meal (WC Product) was ordered for a specific Chef/Event (WC Category), so he could tell the chefs how much food to prepare before the pickup date. Each meal (WC Product) could have WC Variations as well. For example, there could be a WC Product/meal item like Dinner Rolls, and it could have Variations like: Pack of 2, Pack of 4, Pack of 6.
    
    Because of the recurring Chef/Event situation, the orders needed to be filtered by date as well.
</div>

## Solution

<div style="max-width: 600px">
    I created a template that contains a form. The form has a select input that pulls in all WC Categories, and a date range selector (jQuery UI). Once a user submits the form, a bunch of checks using form data create multiple database queries (MySQL) through the global `$wpdb` variable provided by WordPress.
    
    The form then generates a table that lists each Product (which is then broken down into a sublist of Variations). Each Product and Variation have a count beside them.
</div>

# Installation

# Credits

Badges in this README.md provided by [shields.io](https://shields.io/#your-badge).
