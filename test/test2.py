import csv

has_dot = 0
total = 0

with open('../data/test_data/polypoint_testdata.csv') as csvfile:
    reader = csv.DictReader(csvfile)
    for row in reader:
        print(row['email'])
        if row['email'] != '':
            total += 1
            if 'info' in row['email'] or 'support' in row['email']:
                has_dot += 1

print('has dot: ' + str(has_dot))
print('total: ' + str(total))

