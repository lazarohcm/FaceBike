insert into rotas (nome, dias, horario, criador, caminho) values ('Passeio do Bequimão','Segunda e Terça','18h','Lazaro',ST_GeomFromText('LINESTRING(-4931331.061150589 -281930.02885805484,-4931120.859322804 -282617.9621126214,-4931655.9185208 -282498.5292559258,-4931340.615779124 -281931.3938049885)', 3857));
-- delete from rota where id = 6;

-- SELECT AddGeometryColumn ('rota','inicio',3857,'POINT',2);
-- SELECT AddGeometryColumn ('rota','fim',3857,'POINT',2);
-- SELECT updateGeometrySRID('rotas', 'inicio',3857);
-- SELECT UpdateGeometrySRID('roads','geom',4326);