<x-layouts.app title="Completar Registro">
    <div style="max-width:800px;">
        <h1 class="page-title">Completar Registro de Postulante</h1>
        <p class="page-desc">Completá tus datos personales y seleccioná tus carreras para postularte al CUP FICCT.</p>

        @if (session('status'))
            <div class="success">{{ session('status') }}</div>
        @endif

        @if ($errors->any())
            <div class="error">
                <ul style="margin:0;padding-left:18px;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if (!$prepostulante)
            <div class="card" style="padding:40px;text-align:center;">
                <p style="color:#64748b;">No se encontró tu registro de prepostulante vinculado a este usuario.</p>
            </div>
        @else
            <div class="card" style="padding:24px;">
                <form method="POST" action="{{ route('prepostulante.registro.store') }}">
                    @csrf

                    <h3 style="font-size:15px;margin:0 0 16px;color:#0f172a;">🏫 Selección de carreras</h3>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:24px;">
                        <div>
                            <label for="carrera_primera_opcion">Primera opción *</label>
                            <select name="carrera_primera_opcion" id="carrera_primera_opcion" required>
                                <option value="">Seleccioná una carrera</option>
                                @foreach ($carreras as $c)
                                    <option value="{{ $c->id_carrera }}" {{ old('carrera_primera_opcion', $datosRegistro?->carrera_primera_opcion) == $c->id_carrera ? 'selected' : '' }}>
                                        {{ $c->nombre_carrera }} ({{ $c->codigo_carrera }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="carrera_segunda_opcion">Segunda opción</label>
                            <select name="carrera_segunda_opcion" id="carrera_segunda_opcion">
                                <option value="">Seleccioná una carrera</option>
                                @foreach ($carreras as $c)
                                    <option value="{{ $c->id_carrera }}" {{ old('carrera_segunda_opcion', $datosRegistro?->carrera_segunda_opcion) == $c->id_carrera ? 'selected' : '' }}>
                                        {{ $c->nombre_carrera }} ({{ $c->codigo_carrera }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <h3 style="font-size:15px;margin:0 0 16px;color:#0f172a;">👤 Datos personales</h3>
                    <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:14px;margin-bottom:24px;">
                        <div>
                            <label for="fecha_nacimiento">Fecha de nacimiento *</label>
                            <input type="date" name="fecha_nacimiento" id="fecha_nacimiento" value="{{ old('fecha_nacimiento', $datosRegistro?->fecha_nacimiento?->format('Y-m-d')) }}" required>
                        </div>
                        <div>
                            <label for="sexo">Sexo *</label>
                            <select name="sexo" id="sexo" required>
                                <option value="">Seleccioná</option>
                                <option value="masculino" {{ old('sexo', $datosRegistro?->sexo) === 'masculino' ? 'selected' : '' }}>Masculino</option>
                                <option value="femenino" {{ old('sexo', $datosRegistro?->sexo) === 'femenino' ? 'selected' : '' }}>Femenino</option>
                            </select>
                        </div>
                        <div>
                            <label for="telefono">Teléfono *</label>
                            <input type="text" name="telefono" id="telefono" value="{{ old('telefono', $datosRegistro?->telefono) }}" placeholder="Ej: 78912345" required>
                        </div>
                    </div>

                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:24px;">
                        <div>
                            <label for="ciudad">Ciudad *</label>
                            <input type="text" name="ciudad" id="ciudad" value="{{ old('ciudad', $datosRegistro?->ciudad) }}" placeholder="Ej: Santa Cruz" required>
                        </div>
                        <div>
                            <label for="colegio_procedencia">Colegio de procedencia *</label>
                            <input type="text" name="colegio_procedencia" id="colegio_procedencia" value="{{ old('colegio_procedencia', $datosRegistro?->colegio_procedencia) }}" placeholder="Ej: Colegio Nacional" required>
                        </div>
                    </div>

                    <div style="margin-bottom:20px;">
                        <label for="direccion">Dirección *</label>
                        <textarea name="direccion" id="direccion" rows="2" required style="resize:vertical;">{{ old('direccion', $datosRegistro?->direccion) }}</textarea>
                    </div>

                    <div style="margin-bottom:24px;display:flex;align-items:center;gap:8px;">
                        <input type="checkbox" name="titulo_bachiller" id="titulo_bachiller" value="1" {{ old('titulo_bachiller', $datosRegistro?->titulo_bachiller) ? 'checked' : '' }} style="width:auto;">
                        <label for="titulo_bachiller" style="margin:0;cursor:pointer;">Poseo título de bachiller</label>
                    </div>

                    <div style="display:flex;justify-content:flex-end;gap:10px;border-top:1px solid #e2e8f0;padding-top:20px;">
                        <button type="submit" class="button">💾 Guardar datos de registro</button>
                    </div>
                </form>
            </div>

            {{-- Resumen del estado --}}
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-top:20px;">
                <div class="card" style="padding:16px;text-align:center;">
                    <div style="font-size:12px;color:#64748b;">📋 Datos de registro</div>
                    <div style="font-size:18px;font-weight:700;margin-top:4px;color:{{ $datosRegistro ? '#059669' : '#f59e0b' }};">
                        {{ $datosRegistro ? '✅ Completos' : '⏳ Pendiente' }}
                    </div>
                </div>
                <div class="card" style="padding:16px;text-align:center;">
                    <div style="font-size:12px;color:#64748b;">💳 Pago de inscripción</div>
                    <div style="font-size:18px;font-weight:700;margin-top:4px;">
                        <a href="{{ route('prepostulante.pagos.index') }}" style="color:#1e40af;text-decoration:underline;">Ir a Pagos</a>
                    </div>
                </div>
            </div>
        @endif
    </div>
</x-layouts.app>
