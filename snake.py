#!/usr/bin/python3
import math
import time
from array import *
import os
from os import path
import asyncio
from pyartnet import ArtNetNode

snake = 3
lenght = 450 * 4
tabpos = array('L', [])

async def main():
    # Run this code in your async function
    node = ArtNetNode('192.168.100.250', 6454)

    # Create universe 0

    universe = []
    i = 0
    while i < lenght + snake * 4:
        universe.append(node.add_universe(i // 512))
        i += 512

    i = 0
    while i < lenght + snake * 4:
        universe[i // 512].add_channel(start=i % 512 + 1, width=4)
        i += 4

    while True:
        snakeNew()
        snakeDelte()
        snakeMove(universe)
        i = 0
        while i < lenght:
            universe[i // 512].send_data()
            i += 512
        time.sleep(0.005)


def snakeup(universe, pos):
    i = 0

    while i < snake and (pos - i) >= 0:
        # print("A"+str(((pos-i)*4+1)%512))
        channel = universe[((pos - i) * 4 + 1) // 512].get_channel("" + str(((pos - i) * 4 + 1) % 512) + "/4")
        channel.set_values([0, 0, 0,  255 * (1 - math.log(i+1) / math.log(snake))])
        i += 1
    if (pos - snake) >= 0:
        # print("E"+str(((pos-snake)*4+1)%512))
        channel = universe[((pos - snake) * 4 + 1) // 512].get_channel("" + str(((pos - snake) * 4 + 1) % 512) + "/4")
        channel.set_values([0, 0, 0, 0])


# print("end")

def snakeNew():
    if path.isfile('dirExchange/snake'):
        tabpos.append(0)
        os.unlink('dirExchange/snake')


def snakeDelte():
    if len(tabpos) > 0:
        if tabpos[0] > lenght / 4:
            tabpos.pop(0)


def snakeMove(universe):
    for i in range(len(tabpos)):
        snakeup(universe, tabpos[i])
        tabpos[i] += 1


asyncio.run(main())
