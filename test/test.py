import csv
import requests
import urllib.parse
import sys

firsname_detected = 0
lastname_detected = 0
gender_detected = 0
total = 0

with open('../data/test_data/polypoint_testdata.csv') as csvfile:
    reader = csv.DictReader(csvfile)
    for row in reader:
        total += 1
        try:
            string = urllib.parse.quote(row['firstname'] + ' ' + row['lastname'])
            req = requests.get('https://leadfinder.ch/contacts.php?string='+string)
            person = req.json()
            if 'firstname' in person and row['firstname'] == person['firstname']['value']:
                firsname_detected += 1
            else:
                print('firstname not detected: ' + row['firstname'] + ' ' + row['lastname'])

            if 'firstname' in person and row['gender'] == person['firstname']['gender']:
                gender_detected += 1
            else:
                print('gender not detected: ' +row['firstname'] + ' ' + row['lastname'])

            if 'lastname' in person and row['lastname'] == person['lastname']['value']:
                lastname_detected += 1
            else:
                print('lastname not detected: ' + row['firstname'] + ' ' + row['lastname'])
        except:
            print('error with: ' + row['firstname'] + ' ' + row['lastname'])

print('detected firstnames: ' + str(firsname_detected))
print('detected lastnames: ' + str(lastname_detected))
print('detected genders: ' + str(gender_detected))
print('total: ' + str(total))
