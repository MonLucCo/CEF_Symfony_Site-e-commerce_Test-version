# 📄 Flux complet de la session d’achat

**version :** _(Document spécialisé — Flux complet de la session d’achat)_

Ce document décrit le fonctionnement interne du flux d’achat, depuis le panier jusqu’à l’email de confirmation.

---

## 1. Préconditions

- Utilisateur connecté (ROLE_USER)
- Utilisateur vérifié (IS_VERIFIED)
- Panier contenant au moins un article avec quantité > 0

---

## 2. Étapes du flux

### 2.1. Panier (`/cart`)

- Affichage des items
- Vérification du stock
- Bouton “Finaliser ma commande”

---

### 2.2. Checkout Stripe (`/order/checkout`)

- OrderService::prepareCheckout()
- Vérification des quantités
- Construction des `line_items`
- StripeService::createCheckoutSession()
- Redirection vers Stripe

---

### 2.3. Paiement Stripe

- L’utilisateur saisit une carte de test
- Stripe valide le paiement
- Redirection vers `success_url` ou `cancel_url`

---

### 2.4. Succès (`/order/success`)

- OrderService::processSuccess()
  - Filtrage des items à quantité nulle
  - Mise à jour du stock
  - Sauvegarde `last_order`
  - Vidage du panier

---

### 2.5. Email de confirmation (`/order/send-confirmation`)

- OrderService::sendConfirmationEmail()
  - Locale dynamique (`APP_DEFAULT_LOCALE`)
  - Timestamp pour éviter le cache interne du Mailer
  - Nettoyage `last_order`

---

## 3. Schéma global

```js
/cart
    |
    |-- /order/checkout
            |
            |-- Stripe Checkout
            |
            |-- /order/success
                    |
                    |-- /order/send-confirmation
```

---

## 4. Données manipulées

- `cart` (session)
- `last_order_items` (session)
- `last_order_total` (session)
- `Product.stockBySize` (base)

---

## 5. Conclusion

Ce flux respecte les exigences du CEF :  
panier → paiement → mise à jour stock → confirmation utilisateur.

---
