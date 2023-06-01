##UMG/Pimcore Coding Challenge
###SETUP
Install a local environment for Pimcore 10.6x using docker-compose. Details are in the below repo:
* https://github.com/pimcore/skeleton

Using the UI, add a new Data Object Class called `Product` with fields `name` (text), `type` (`select`: values should `CD` or `Vinyl`) and `price` (number).
Create an example data object of this class with any values you see fit.

###SHOPIFY SETUP
Create a Shopify partner account https://partners.shopify.com/
Navigate to Stores > Create Development Store and fill out the form.
Once within the new store, go to Settings > Apps and sales channels > Allow custom apps.
Then press `Develop Apps` top right corner and select `Create an App`
Press `Configure Admin API scopes` and select write_products, then save.
Next, be sure to generate your Access token by clicking Install App. Save your Access Token + API Key and Secret Key.

###CODE
Create a new Symfony Bundle which we will use to extend default logic.
Create an event listener and register this bundle to listen for product `postUpdate` events as described in the Pimcore Documentation.
This event listener will perform the following:
Take the object from the event and get the name, type and price values from the object.
Perform a product create event, publishing the name, type and price values to the Shopify product. Note: you can map name > title, type > product_type and price > price.
You may use either the Shopify REST API or the Shopify GraphQL API.
https://shopify.dev/docs/api/admin-rest/
https://shopify.dev/docs/api/admin-graphql
Once everything is working, you should be able to save a `Product` in the Pimcore UI and see it flow through to your Shopify store.


####BONUS
If you love coding so much that you want MOAR, feel free to extend the Product model to include a SKU field. (type text)
Extend the Product Listener to first perform a query of your shopify store to see if a product variant with that SKU already exists.
If it does exist, update it.
If it does not exist, keep the existing logic to create it.

###NOTES:
Consider safely storing secrets.
Consider Symfony best practices, i.e. dependency injection.
Consider integration failover.
Add some logging to capture the events, you can use \Pimcore\Log\Simple::log() for this.