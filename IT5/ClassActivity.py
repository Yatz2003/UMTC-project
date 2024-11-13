sales_data = [
    [1200, 1300, 1250, 1100, 1400, 1500],
    [1000, 950, 1050, 900, 1200, 1100],  
    [800, 850, 780, 920, 870, 960]       
]
product_totals = [] 
overall_total = 0  

for i, product_sales in enumerate(sales_data):
    product_total = 0 
    print(f"Product {chr(65 + i)} sales data:")

    for month, sales in enumerate(product_sales, start=1):
        print(f"  Month {month}: ${sales}")
        product_total += sales

    product_totals.append(product_total)
    overall_total += product_total
    print(f"  Total for Product {chr(65 + i)}: ${product_total}\n")
print("Overall total sales across all products:", overall_total)
