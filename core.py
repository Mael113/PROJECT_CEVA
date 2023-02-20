import RPi.GPIO as GPIO
import time

GPIO.setmode(GPIO.BOARD)
GPIO.setup(40, GPIO.IN, pull_up_down=GPIO.PUD_UP)

counter = 0

def increment_counter(channel):
    global counter
    state = GPIO.input(40)
    if state == False:
        counter += 1
        with open('dirExchange/score.txt', 'w+') as f:
            f.write(str(counter))
        with open('dirExchange/snake', 'w+') as f:
            f.write(str(counter))


GPIO.add_event_detect(40, GPIO.BOTH, callback=increment_counter, bouncetime=200)

try:
    while True:
        time.sleep(1)
except KeyboardInterrupt:
    GPIO.cleanup()