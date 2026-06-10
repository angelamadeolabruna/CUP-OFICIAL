<x-layouts.app title="Editar Postulante">
    <div style="max-width:900px;">
        <div style="margin-bottom:18px;">
            <a href="{{ route('academico.postulantes.index') }}" class="button button-ghost button-sm">← Volver al listado</a>
        </div>

        <div class="card">
            <h1 class="page-title">Editar Postulante</h1>
            <p class="page-desc">
                Modificá los datos de <strong>{{ $postulante->prepostulante?->nombres ?? '' }} {{ $postulante->prepostulante?->apellidos ?? '' }}</strong>
                (CI: {{ $postulante->prepostulante?->ci ?? '—' }})
            </p>

            @if (session('status'))
                <div class="alert alert-success">{{ session('status') }}</div>
            @endif
            @if ($errors->any())
                <div class="alert alert-error">
                    <ul style="margin:0;padding-left:18px;">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('academico.postulantes.update', $postulante->id_postulante) }}">
                @csrf
                @method('PUT')

                <h3 style="margin:20px 0 10px;font-size:15px;color:#1f2937;border-bottom:1px solid #e2e8f0;padding-bottom:6px;">Datos Personales</h3>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
                    <div>
                        <label for="nombres">Nombres</label>
                        <input type="text" name="nombres" id="nombres"
                               value="{{ old('nombres', $postulante->prepostulante?->nombres ?? '') }}">
                        @error('nombres') <span class="error">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label for="apellidos">Apellidos</label>
                        <input type="text" name="apellidos" id="apellidos"
                               value="{{ old('apellidos', $postulante->prepostulante?->apellidos ?? '') }}">
                        @error('apellidos') <span class="error">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label for="fecha_nacimiento">Fecha de Nacimiento</label>
                        <input type="date" name="fecha_nacimiento" id="fecha_nacimiento"
                               value="{{ old('fecha_nacimiento', $postulante->fecha_nacimiento?->format('Y-m-d') ?? '') }}">
                        @error('fecha_nacimiento') <span class="error">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label for="sexo">Sexo</label>
                        <select name="sexo" id="sexo">
                            <option value="">— Seleccionar —</option>
                            <option value="M" {{ old('sexo', $postulante->sexo) === 'M' ? 'selected' : '' }}>Masculino</option>
                            <option value="F" {{ old('sexo', $postulante->sexo) === 'F' ? 'selected' : '' }}>Femenino</option>
                            <option value="Otro" {{ old('sexo', $postulante->sexo) === 'Otro' ? 'selected' : '' }}>Otro</option>
                        </select>
                        @error('sexo') <span class="error">{{ $message }}</span> @enderror
                    </div>
                </div>

                <h3 style="margin:20px 0 10px;font-size:15px;color:#1f2937;border-bottom:1px solid #e2e8f0;padding-bottom:6px;">Contacto</h3>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
                    <div>
                        <label for="correo">Correo Electrónico</label>
                        <input type="email" name="correo" id="correo"
                               value="{{ old('correo', $postulante->correo) }}">
                        @error('correo') <span class="error">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label for="telefono">Teléfono</label>
                        <input type="text" name="telefono" id="telefono"
                               value="{{ old('telefono', $postulante->telefono) }}">
                        @error('telefono') <span class="error">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label for="direccion">Dirección</label>
                        <input type="text" name="direccion" id="direccion"
                               value="{{ old('direccion', $postulante->direccion) }}">
                        @error('direccion') <span class="error">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label for="ciudad">Ciudad</label>
                        <input type="text" name="ciudad" id="ciudad"
                               value="{{ old('ciudad', $postulante->ciudad) }}">
                        @error('ciudad') <span class="error">{{ $message }}</span> @enderror
                    </div>
                </div>

                <h3 style="margin:20px 0 10px;font-size:15px;color:#1f2937;border-bottom:1px solid #e2e8f0;padding-bottom:6px;">Datos Académicos</h3>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
                    <div>
                        <label for="colegio_procedencia">Colegio de Procedencia</label>
                        <input type="text" name="colegio_procedencia" id="colegio_procedencia"
                               value="{{ old('colegio_procedencia', $postulante->colegio_procedencia) }}">
                        @error('colegio_procedencia') <span class="error">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label for="titulo_bachiller" style="display:flex;align-items:center;gap:8px;margin-top:20px;">
                            <input type="checkbox" name="titulo_bachiller" id="titulo_bachiller" value="1"
                                   {{ old('titulo_bachiller', $postulante->titulo_bachiller) ? 'checked' : '' }}>
                            Título de Bachiller
                        </label>
                        @error('titulo_bachiller') <span class="error">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label for="carrera_primera_opcion">Primera Opción</label>
                        <select name="carrera_primera_opcion" id="carrera_primera_opcion">
                            <option value="">— Seleccionar —</option>
                            @foreach ($carreras as $c)
                                <option value="{{ $c->id_carrera }}"
                                    {{ old('carrera_primera_opcion', $postulante->carrera_primera_opcion) == $c->id_carrera ? 'selected' : '' }}>
                                    {{ $c->codigo_carrera }} — {{ $c->nombre_carrera }}
                                </option>
                            @endforeach
                        </select>
                        @error('carrera_primera_opcion') <span class="error">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label for="carrera_segunda_opcion">Segunda Opción</label>
                        <select name="carrera_segunda_opcion" id="carrera_segunda_opcion">
                            <option value="">— Seleccionar —</option>
                            @foreach ($carreras as $c)
                                <option value="{{ $c->id_carrera }}"
                                    {{ old('carrera_segunda_opcion', $postulante->carrera_segunda_opcion) == $c->id_carrera ? 'selected' : '' }}>
                                    {{ $c->codigo_carrera }} — {{ $c->nombre_carrera }}
                                </option>
                            @endforeach
                        </select>
                        @error('carrera_segunda_opcion') <span class="error">{{ $message }}</span> @enderror
                    </div>
                </div>

                <h3 style="margin:20px 0 10px;font-size:15px;color:#1f2937;border-bottom:1px solid #e2e8f0;padding-bottom:6px;">Estado</h3>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
                    <div>
                        <label for="estado_postulante">Estado del Postulante</label>
                        <select name="estado_postulante" id="estado_postulante">
                            <option value="inscrito" {{ old('estado_postulante', $postulante->estado_postulante) === 'inscrito' ? 'selected' : '' }}>Inscrito</option>
                            <option value="baja" {{ old('estado_postulante', $postulante->estado_postulante) === 'baja' ? 'selected' : '' }}>Baja</option>
                        </select>
                        @error('estado_postulante') <span class="error">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div style="margin-top:24px;display:flex;gap:10px;">
                    <button type="submit" class="button">Guardar Cambios</button>
                    <a href="{{ route('academico.postulantes.index') }}" class="button button-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</x-layouts.app>
