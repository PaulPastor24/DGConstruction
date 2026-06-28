'use client'

import { makeStyles } from '@fluentui/react-components'

const useStyles = makeStyles({
  root: {
    backgroundColor: '#FFFFFF',
    borderRadius: '16px',
    padding: '32px',
    boxShadow: '0 1px 3px rgba(0, 0, 0, 0.06)',
    border: '1px solid rgba(0, 0, 0, 0.08)',
  },
  header: {
    marginBottom: '28px',
    paddingBottom: '20px',
    borderBottom: '1px solid rgba(0, 0, 0, 0.08)',
  },
  title: {
    fontSize: '24px',
    fontWeight: 700,
    color: '#2F6B3C',
    marginBottom: '6px',
    letterSpacing: '-0.3px',
  },
  subtitle: {
    fontSize: '14px',
    color: '#999999',
    fontWeight: 400,
  },
  grid: {
    display: 'grid',
    gridTemplateColumns: 'repeat(4, 1fr)',
    gap: '24px',
    marginBottom: '32px',
    '@media (max-width: 1200px)': {
      gridTemplateColumns: 'repeat(2, 1fr)',
    },
    '@media (max-width: 768px)': {
      gridTemplateColumns: '1fr',
    },
  },
  item: {
    display: 'flex',
    flexDirection: 'column',
  },
  label: {
    fontSize: '13px',
    color: '#999999',
    fontWeight: 700,
    textTransform: 'uppercase',
    letterSpacing: '0.5px',
    marginBottom: '8px',
  },
  value: {
    fontSize: '16px',
    fontWeight: 700,
    color: '#333333',
  },
  badge: {
    display: 'inline-block',
    paddingTop: '6px',
    paddingBottom: '6px',
    paddingLeft: '12px',
    paddingRight: '12px',
    borderRadius: '20px',
    fontSize: '12px',
    fontWeight: 700,
    backgroundColor: 'rgba(102, 187, 106, 0.15)',
    color: '#2F6B3C',
    width: 'fit-content',
    marginBottom: '24px',
  },
  progressSection: {
    display: 'flex',
    flexDirection: 'column',
    gap: '16px',
    marginBottom: '32px',
  },
  progressLabel: {
    display: 'flex',
    justifyContent: 'space-between',
    alignItems: 'center',
  },
  progressBar: {
    height: '14px',
    backgroundColor: '#EEEEEA',
    borderRadius: '10px',
    overflow: 'hidden',
    boxShadow: 'inset 0 1px 2px rgba(0, 0, 0, 0.04)',
  },
  progressFill: {
    height: '100%',
    width: '72%',
    backgroundColor: '#66BB6A',
    transition: 'width 0.6s ease',
    borderRadius: '10px',
  },
  phaseStrip: {
    display: 'flex',
    alignItems: 'center',
    gap: '8px',
    flexWrap: 'wrap',
  },
  phaseTag: {
    paddingTop: '8px',
    paddingBottom: '8px',
    paddingLeft: '14px',
    paddingRight: '14px',
    borderRadius: '12px',
    fontSize: '13px',
    fontWeight: 600,
    backgroundColor: '#EEEEEA',
    color: '#666666',
  },
  phaseTagActive: {
    backgroundColor: 'rgba(102, 187, 106, 0.2)',
    color: '#2F6B3C',
    fontWeight: 700,
  },
  separator: {
    color: '#DDDDDD',
    fontSize: '14px',
  },
})

export function ProjectTimelineSummary() {
  const styles = useStyles()

  return (
    <div className={styles.root}>
      <div className={styles.header}>
        <div className={styles.title}>Westfield Plaza Development</div>
        <div className={styles.subtitle}>Your current supervision assignment</div>
      </div>

      <div className={styles.badge}>On Schedule</div>

      <div className={styles.grid}>
        <div className={styles.item}>
          <div className={styles.label}>Project Code</div>
          <div className={styles.value}>WPD-2024-001</div>
        </div>
        <div className={styles.item}>
          <div className={styles.label}>Client</div>
          <div className={styles.value}>Metro Developers</div>
        </div>
        <div className={styles.item}>
          <div className={styles.label}>Location</div>
          <div className={styles.value}>Downtown District</div>
        </div>
        <div className={styles.item}>
          <div className={styles.label}>Supervisor</div>
          <div className={styles.value}>John Mitchell</div>
        </div>
      </div>

      <div className={styles.progressSection}>
        <div className={styles.progressLabel}>
          <span className={styles.label} style={{ margin: 0 }}>Overall Progress</span>
          <span style={{ fontSize: '24px', fontWeight: 700, color: '#2F6B3C' }}>72%</span>
        </div>
        <div className={styles.progressBar}>
          <div className={styles.progressFill} />
        </div>
      </div>

      <div>
        <div className={styles.label} style={{ marginBottom: '12px' }}>Construction Phases</div>
        <div className={styles.phaseStrip}>
          <div className={styles.phaseTag}>Foundation</div>
          <span className={styles.separator}>•</span>
          <div className={styles.phaseTag}>Framing</div>
          <span className={styles.separator}>•</span>
          <div className={`${styles.phaseTag} ${styles.phaseTagActive}`}>Roofing</div>
          <span className={styles.separator}>•</span>
          <div className={styles.phaseTag}>Finishing</div>
          <span className={styles.separator}>•</span>
          <div className={styles.phaseTag}>Inspection</div>
        </div>
      </div>
    </div>
  )
}
