from fastapi import FastAPI, HTTPException
from pydantic import BaseModel
from typing import List
import mysql.connector
from mysql.connector import Error

app = FastAPI()

class Car(BaseModel):
    id: int
    license_plate: str
    distance: float


database = []


def create_connection():
    try:
        connection = mysql.connector.connect(
            host='151.106.124.154',
            database='u583789277_wag7',
            user='u583789277_wag7',
            password='2567Concept' 
        )
        if connection.is_connected():
            return connection
    except Error as e:
        print(f"Error while connecting to MySQL: {e}")
        return None


def insert_data(connection, card_id, distance):
    try:
        cursor = connection.cursor()
        query = "INSERT INTO distance_data (distance, card_id) VALUES (%s, %s)"
        cursor.execute(query, (distance, card_id))
        connection.commit()
    except Error as e:
        print(f"Error while inserting data into MySQL: {e}")
    finally:
        cursor.close()

@app.post("/data")
async def receive_data(data: Car):
    connection = create_connection()
    if connection:
        try:
            insert_data(connection, data.id, data.distance)
        except Exception as e:
            print(f"Error processing data: {e}")
            raise HTTPException(status_code=500, detail="Internal Server Error")
        finally:
            connection.close()
    else:
        raise HTTPException(status_code=500, detail="Failed to connect to the database")
    
    print(f"License Plate: {data.license_plate}, Card ID: {data.id}, Distance: {data.distance}")
    return {"status": "success"}


@app.post("/cars/", response_model=Car)
async def create_car(car: Car):

    database.append(car)
    return car


@app.get("/cars/", response_model=List[Car])
async def get_cars():
    return database


@app.get("/cars/{car_id}", response_model=Car)
async def get_car(car_id: int):
    for car in database:
        if car.id == car_id:
            return car
    raise HTTPException(status_code=404, detail="Car not found")


@app.put("/cars/{car_id}", response_model=Car)
async def update_car(car_id: int, car: Car):
    for idx, existing_car in enumerate(database):
        if existing_car.id == car_id:
            database[idx] = car
            return car
    raise HTTPException(status_code=404, detail="Car not found")


@app.delete("/cars/{car_id}", response_model=Car)
async def delete_car(car_id: int):
    for idx, existing_car in enumerate(database):
        if existing_car.id == car_id:
            deleted_car = database.pop(idx)
            return deleted_car
    raise HTTPException(status_code=404, detail="Car not found")

if __name__ == "__main__":
    import uvicorn
    uvicorn.run(app, host="172.20.10.5", port=8000)
    
