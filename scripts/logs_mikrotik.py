#!/usr/bin/env python3
import mysql.connector
import time
from datetime import datetime
import paramiko

def guardar_log(conexion, id_mikrotik, fecha, tipo, topico, mensaje):
    try:
        cursor = conexion.cursor()
        cursor.execute("""
            INSERT INTO logs_mikrotiks (id_mikrotik, fecha_hora, tipo, topico, mensaje)
            VALUES (%s, %s, %s, %s, %s)
        """, (id_mikrotik, fecha, tipo, topico, mensaje))
        conexion.commit()
        cursor.close()
    except Exception as e:
        print(f"⚠️ No se pudo guardar log de MikroTik: {e}")

def obtener_logs_mikrotik(credenciales):
    username, password, host, port = credenciales
    try:
        cliente_ssh = paramiko.SSHClient()
        cliente_ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
        cliente_ssh.connect(host, port=port, username=username, password=password, timeout=5)
        stdin, stdout, stderr = cliente_ssh.exec_command("/log/print")
        salida = stdout.read().decode()
        cliente_ssh.close()
        return salida.strip().split('\n')
    except Exception as e:
        print(f"❌ Error SSH al obtener logs de {host}: {e}")
        return []

def procesar_base_datos(base_datos):
    try:
        conexion = mysql.connector.connect(
            host="softwarescobedo.com.mx",
            user="adminet",
            password="MinuzaFea265/",
            database=base_datos
        )
        cursor = conexion.cursor(dictionary=True)
        cursor.execute("SELECT * FROM credenciales_microtik")
        mikrotiks = cursor.fetchall()

        for mikrotik in mikrotiks:
            credenciales = (
                mikrotik['username'],
                mikrotik['password'],
                mikrotik['ip'],
                mikrotik['port']
            )
            logs = obtener_logs_mikrotik(credenciales)
            for entrada in logs:
                try:
                    # Formato esperado: "2025-06-11 07:16:01 tipo1,tipo2,... mensaje"
                    partes = entrada.strip().split(" ", 3)
                    if len(partes) < 4:
                        continue

                    fecha_str = partes[0] + " " + partes[1]
                    fecha_log = datetime.strptime(fecha_str, "%Y-%m-%d %H:%M:%S")

                    tipo = partes[2].strip()
                    mensaje = partes[3].strip()

                    guardar_log(conexion, mikrotik['id'], fecha_log, tipo, "", mensaje)
                except Exception as e:
                    print(f"⚠️ No se pudo procesar una línea de log: {e}")
        cursor.close()
        conexion.close()
    except Exception as e:
        print(f"❌ Error en base '{base_datos}': {e}")

if __name__ == "__main__":
    print("📡 Recolección de logs MikroTik iniciada.")
    while True:
        try:
            conexion_global = mysql.connector.connect(
                host="softwarescobedo.com.mx",
                user="adminet",
                password="MinuzaFea265/",
                database="adminet_global"
            )
            cursor_global = conexion_global.cursor(dictionary=True)
            cursor_global.execute("SELECT base_datos FROM usuarios_empresas")
            bases = cursor_global.fetchall()
            cursor_global.close()
            conexion_global.close()

            for entrada in bases:
                base = entrada['base_datos']
                print(f"🔍 Leyendo logs de base: {base}")
                procesar_base_datos(base)

        except Exception as e:
            print(f"❌ Error al conectar a adminet_global: {e}")

        for i in range(120, 0, -1):
            print(f"⏳ Esperando {i} segundos...", end="\r", flush=True)
            time.sleep(1)
        print("\n")
