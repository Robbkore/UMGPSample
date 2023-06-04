# 💥 Code Sample Submission 💥

Welcome! Thank you for taking the time to review my code sample submission.

## ✔ Setup

I used the Docker setup for the sample, instructions I followed are from the Pimcore skeleton 
project and can be found in PIMCORE.md.  

For extra reference see the files in /pimcore-extra. I've added the class definition for the 
Product class, as well as an export of the products I created and used to test integration. 
There is also an export from the shopify developer storefront in the folder, purely for reference.

I used env.dev.local to store my secrets, so they are not in the commit history anywhere, 
cp env.dev.local.sample to env.dev.local and change the values to work.

## ✔ Usage

Once you add a new product through the Pimcore admin tool, the listener should either create 
or update an existing product, if it's published.

