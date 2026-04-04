import csv
import json
import sys
import time
import paho.mqtt.client as mqtt

def dataReplay(file_path, topic):
    broker_address = "localhost"
    port = 1883
    client = mqtt.Client()
    client.connect(broker_address, port, 60)

    while True:
        with open(file_path, 'r') as file:
            data = csv.DictReader(file)
            for entry in data:
                data_payload = json.dumps(entry)
                client.publish(topic, data_payload, qos=0)
                print(f"Published: {data_payload} to topic: {topic}")
                time.sleep(1) 

def main(file, topic):
    dataReplay(file, topic)

if __name__ == "__main__":
    file = sys.argv[1]
    topic = sys.argv[2]
    main(file, topic)